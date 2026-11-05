<?php

namespace App\Controllers\Api;

/**
 * Pesanan dari aplikasi. Aturan harga, Tripay, saldo, Digiflazz/ApiGames
 * mengikuti Games::index (checkout web). Harga SELALU dihitung ulang di server.
 */
class Orders extends BaseApi
{
    // ------------------------------------------------------------------ buat
    public function create()
    {
        if (!$this->throttle('order', 10, 60)) {
            return $this->fail('Terlalu banyak permintaan, coba lagi sebentar.', 429);
        }
        $in = $this->input();
        $user = $this->user(); // null = tamu
        $f = is_array($in['fields'] ?? null) ? $in['fields'] : [];

        $slug = $this->clean($in['game_slug'] ?? '');
        $pid = (int) ($in['product_id'] ?? 0);
        $mcode = $this->clean($in['method_code'] ?? '');
        $uid = $this->clean($f['user_id'] ?? '');
        $zone = $this->clean($f['zone_id'] ?? '');
        $wa = preg_replace('/\D/', '', (string) ($in['whatsapp'] ?? ''));
        $nick = preg_replace('/[^\p{L}\p{N}\s]/u', '', $this->clean($in['nickname'] ?? ''));

        $game = $this->db->table('games')->where('slug', $slug)->get()->getRowArray();
        if (!$game || !$this->gameSupported($game)) {
            return $this->fail('Game tidak ditemukan.', 404);
        }
        if ($uid === '' || strlen($uid) > 60 || ($this->needsZone($game) && $zone === '')) {
            return $this->fail('ID Player harus diisi.');
        }
        if (!$this->needsZone($game)) {
            $zone = '1';
        }
        if (strlen($wa) < 10 || strlen($wa) > 15) {
            return $this->fail('Nomor WhatsApp tidak sesuai.');
        }

        $product = $this->db->table('product')->where('id', $pid)->get()->getRowArray();
        if (!$product || (int) $product['games_id'] !== (int) $game['id']) {
            return $this->fail('Produk tidak ditemukan.', 404);
        }
        if ($product['status'] !== 'On') {
            return $this->fail('Produk sedang gangguan.');
        }

        // ---- metode
        if ($mcode === 'balance') {
            if ($this->M_Base->u_get('pay_balance') !== 'Y') {
                return $this->fail('Metode pembayaran tidak ditemukan.', 404);
            }
            if (!$user) {
                return $this->fail('Silakan login dahulu untuk memakai saldo.', 401);
            }
            $method = ['id' => 10001, 'status' => 'On', 'provider' => 'Balance', 'method' => 'Saldo Akun',
                'uniq' => 'N', 'code' => 'Balance', 'type' => '', 'rek' => ''];
        } else {
            $method = $this->findMethod($mcode);
            if (!$method) {
                return $this->fail('Metode pembayaran tidak tersedia.', 404);
            }
            if (!in_array($method['provider'], ['Tripay'], true)) {
                return $this->fail('Metode ini hanya tersedia di website.');
            }
        }

        // ---- harga (server)
        $price = (int) ceil($this->priceFor($product, $user));
        if ($price < 1) {
            return $this->fail('Harga produk belum diatur.');
        }

        // ---- cegah pesanan ganda (30 detik)
        $dupe = $this->db->table('orders')->where('user_id', $uid)->where('zone_id', $zone)
            ->where('product_id', $product['id'])->where('method_id', $method['id'])->where('wa', $wa)
            ->where('date_create >=', date('Y-m-d H:i:s', time() - 30))->countAllResults();
        if ($dupe > 0) {
            return $this->fail('Pesanan yang sama baru saja dibuat. Cek riwayat pesanan Anda.', 409);
        }

        $orderId = $this->newInvoice();
        $status = 'Pending';
        $ket = 'Menunggu Pembayaran';
        $payCode = '';
        $target = ($zone !== '' && $zone !== '1') ? $uid . $zone : $uid;
        $deducted = false;

        try {
            if ($method['provider'] === 'Tripay') {
                $r = $this->tripayCreate($method['code'], $orderId, $price, $user['username'] ?? 'Default', $wa, $product);
                if (!is_array($r) || empty($r['success'])) {
                    return $this->fail('Pembayaran gagal dibuat: ' . ($r['message'] ?? 'tidak dapat terhubung ke Tripay'), 502);
                }
                $d = $r['data'];
                $payCode = !empty($d['qr_url']) ? $d['qr_url'] : (!empty($d['pay_code']) ? $d['pay_code'] : ($d['checkout_url'] ?? ''));
                if ($payCode === '') {
                    return $this->fail('Pembayaran gagal dibuat.', 502);
                }
            } else { // saldo: potong atomik agar tidak bisa minus / dobel
                $this->db->query('UPDATE users SET balance = balance - ? WHERE id = ? AND balance >= ?',
                    [$price, $user['id'], $price]);
                if ($this->db->affectedRows() !== 1) {
                    return $this->fail('Saldo tidak mencukupi.');
                }
                $deducted = true;
                $payCode = 'Saldo Akun';
                $status = 'Processing';

                if ($product['provider'] === 'DF') {
                    $res = $this->M_Base->df_order($product['sku'], $target, $orderId);
                    if (isset($res['data'])) {
                        $ket = ($res['data']['status'] ?? '') === 'Gagal'
                            ? ($res['data']['message'] ?? 'Gagal')
                            : (($res['data']['sn'] ?? '') !== '' ? $res['data']['sn'] : ($res['data']['message'] ?? ''));
                    } else {
                        $ket = 'Failed Order';
                    }
                } elseif ($product['provider'] === 'AG') {
                    $res = $this->M_Base->ag_v1_order($product['sku'], $target, $orderId);
                    if (empty($res['status'])) {
                        $ket = $res['error_msg'] ?? 'Failed Order';
                    } else {
                        if (($res['data']['status'] ?? '') === 'Sukses') {
                            $status = 'Success';
                            $this->M_Base->wapisender_sukses($wa, $product['product'], $orderId, $method['method']);
                        }
                        $ket = $res['data']['sn'] ?? '';
                    }
                } elseif ($product['provider'] === 'Manual') {
                    $ket = 'Pesanan siap diproses';
                } else {
                    $ket = 'Provider tidak ditemukan';
                }
            }

            $now = date('Y-m-d H:i:s');
            $ok = $this->db->table('orders')->insert([
                'order_id' => $orderId,
                'username' => $user['username'] ?? '',
                'wa' => $wa,
                'product_id' => $product['id'],
                'product' => $product['product'],
                'price' => $price,
                'raw_price' => $product['raw_price'],
                'user_id' => $uid,
                'zone_id' => $zone,
                'nickname' => $nick,
                'method_id' => $method['id'],
                'method_code' => $method['code'],
                'method' => $method['method'],
                'games' => $game['games'],
                'games_id' => $game['id'],
                'status' => $status,
                'ket' => $ket,
                'trx_id' => $orderId,
                'voucher' => '',
                'payment_gateway' => $method['provider'],
                'payment_code' => $payCode,
                'payment_type' => $method['type'] ?? '',
                'provider' => $product['provider'],
                'date' => date('Y-m-d'),
                'date_create' => $now,
                'date_process' => $now,
            ]);
            if (!$ok) {
                throw new \RuntimeException('insert gagal');
            }
        } catch (\Throwable $e) {
            log_message('error', 'API order gagal: ' . $e->getMessage());
            if ($deducted) { // kembalikan saldo bila pesanan tidak tercatat
                $this->db->query('UPDATE users SET balance = balance + ? WHERE id = ?', [$price, $user['id']]);
            }
            return $this->fail('Pesanan gagal dibuat, silakan coba lagi.', 500);
        }

        if (in_array($status, ['Pending', 'Processing'], true)) {
            $this->M_Base->wapisender_pending($wa, $product['product'], $orderId, $method['method'], $price);
        }

        $row = $this->db->table('orders')->where('order_id', $orderId)->get()->getRowArray();
        return $this->ok($this->format($row), 'Pesanan berhasil dibuat');
    }

    // ---------------------------------------------------------------- lihat
    public function show($invoice = '')
    {
        if (!$this->throttle('ordshow', 120, 60)) {
            return $this->fail('Terlalu banyak permintaan.', 429);
        }
        if (!preg_match('/^INV\d{14}$/', (string) $invoice)) {
            return $this->fail('Pesanan tidak ditemukan.', 404);
        }
        $row = $this->db->table('orders')->where('order_id', $invoice)->get()->getRowArray();
        if (!$row) {
            return $this->fail('Pesanan tidak ditemukan.', 404);
        }
        $row = $this->refreshFromProvider($row);
        return $this->ok($this->format($row));
    }

    public function mine()
    {
        $u = $GLOBALS['api_user'];
        $rows = $this->db->table('orders')->where('username', $u['username'])
            ->orderBy('id', 'DESC')->limit(50)->get()->getResultArray();
        return $this->ok(['orders' => array_map([$this, 'format'], $rows)]);
    }

    // ------------------------------------------------------------- internal
    private function newInvoice(): string
    {
        do {
            $id = 'INV' . date('Ymd') . random_int(100000, 999999);
        } while ($this->db->table('orders')->where('order_id', $id)->countAllResults() > 0);
        return $id;
    }

    private function tripayCreate(string $code, string $orderId, int $price, string $name, string $wa, array $product): ?array
    {
        $merchant = $this->M_Base->u_get('tripay-merchant');
        $data = [
            'method' => $code,
            'merchant_ref' => $orderId,
            'amount' => $price,
            'customer_name' => $name,
            'customer_email' => 'email@domain.com',
            'customer_phone' => $wa,
            'order_items' => [['sku' => $product['sku'], 'name' => $product['product'], 'price' => $price, 'quantity' => 1]],
            'return_url' => base_url('payment/' . $orderId),
            'expired_time' => time() + 86400,
            'signature' => hash_hmac('sha256', $merchant . $orderId . $price, $this->M_Base->u_get('tripay-private')),
        ];
        $ch = curl_init('https://tripay.co.id/api/transaction/create');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $this->M_Base->u_get('tripay-key')],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            CURLOPT_TIMEOUT => 25,
        ]);
        $out = curl_exec($ch);
        curl_close($ch);
        $j = json_decode((string) $out, true);
        return is_array($j) ? $j : null;
    }

    /** Cek status ke Digiflazz/ApiGames untuk pesanan saldo yang masih diproses (seperti halaman bayar web). */
    private function refreshFromProvider(array $o): array
    {
        if ($o['status'] !== 'Processing' || !in_array($o['provider'], ['DF', 'AG'], true)) {
            return $o;
        }
        $cache = cache();
        $lock = 'ordchk_' . $o['id'];
        if ($cache->get($lock)) {
            return $o;
        }
        $cache->save($lock, 1, 15); // paling sering sekali per 15 detik per pesanan

        $set = null;
        if ($o['provider'] === 'DF') {
            $u = $this->M_Base->u_get('digi-user');
            $k = $this->M_Base->u_get('digi-key');
            $sku = $this->db->table('product')->select('sku')->where('id', $o['product_id'])->get()->getRowArray()['sku'] ?? '';
            $r = $this->postJson('https://api.digiflazz.com/v1/transaction', [
                'username' => $u, 'buyer_sku_code' => $sku,
                'customer_no' => $o['user_id'] . $o['zone_id'], 'ref_id' => $o['order_id'],
                'sign' => md5($u . $k . $o['order_id']),
            ]);
            if (isset($r['data']['status'])) {
                if ($r['data']['status'] === 'Gagal') {
                    $set = ['status' => 'Canceled', 'ket' => $r['data']['message'] ?? 'Gagal'];
                } elseif ($r['data']['status'] === 'Sukses') {
                    $sn = $r['data']['sn'] ?? '';
                    $set = ['status' => 'Success', 'ket' => $sn !== '' ? $sn : ($r['data']['message'] ?? 'Sukses')];
                }
            }
        } else {
            $m = $this->M_Base->u_get('ag-merchant');
            $s = $this->M_Base->u_get('ag-secret');
            $r = $this->postJson('https://v1.apigames.id/v2/transaksi/status', [
                'ref_id' => $o['order_id'], 'merchant_id' => $m, 'signature' => md5($m . ':' . $s . ':' . $o['order_id']),
            ]);
            if (($r['status'] ?? 0) && ($r['data']['status'] ?? '') === 'Sukses') {
                $set = ['status' => 'Success', 'ket' => $r['data']['sn'] ?? 'Sukses'];
            }
        }

        if ($set) {
            // Ubah hanya bila masih Processing -> tidak bisa refund dobel
            $this->db->table('orders')->where('id', $o['id'])->where('status', 'Processing')->update($set);
            if ($this->db->affectedRows() === 1) {
                if ($set['status'] === 'Canceled' && (int) $o['method_id'] === 10001 && $o['username'] !== '') {
                    $this->db->query('UPDATE users SET balance = balance + ? WHERE username = ?', [$o['price'], $o['username']]);
                }
                $o = array_merge($o, $set);
            }
        }
        return $o;
    }

    private function postJson(string $url, array $body): ?array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($body),
        ]);
        $out = curl_exec($ch);
        curl_close($ch);
        $j = json_decode((string) $out, true);
        return is_array($j) ? $j : null;
    }

    /** Baris orders -> objek Order sesuai docs/API.md */
    private function format(array $o): array
    {
        $st = $this->mapStatus($o['status']);
        $target = ($o['zone_id'] !== '' && $o['zone_id'] !== '1') ? $o['user_id'] . ' / ' . $o['zone_id'] : $o['user_id'];
        $created = strtotime($o['date_create']);

        $note = '';
        if ($st === 'success') {
            $note = (string) $o['ket']; // SN / kode voucher
        } elseif ($st === 'failed') {
            $note = 'Pesanan gagal diproses. Hubungi admin bila pembayaran Anda sudah terpotong.';
        }

        $payment = null;
        if ($st === 'pending' || $st === 'processing') {
            $code = (string) $o['payment_code'];
            if ($o['payment_gateway'] === 'Balance') {
                $payment = ['type' => 'balance', 'checkout_url' => '', 'qr_url' => '', 'pay_code' => '', 'pay_label' => '', 'expired_at' => null];
            } elseif ($o['payment_gateway'] === 'Tripay') {
                $isUrl = preg_match('#^https?://#', $code) === 1;
                $isQr = $isUrl && strpos($code, '/qr/') !== false;
                $type = $isQr ? 'qris' : ($isUrl ? 'redirect' : ($o['payment_type'] === 'Convenience Store' ? 'retail' : 'va'));
                $payment = [
                    'type' => $type,
                    'checkout_url' => ($isUrl && !$isQr) ? $code : '',
                    'qr_url' => $isQr ? $code : '',
                    'pay_code' => $isUrl ? '' : $code,
                    'pay_label' => $isUrl ? '' : $o['method'],
                    'expired_at' => date('c', $created + 86400),
                ];
            }
        }

        return [
            'invoice' => $o['order_id'],
            'status' => $st,
            'game' => $o['games'],
            'product' => $o['product'],
            'target' => $target,
            'method' => $o['method'],
            'total' => (int) $o['price'],
            'created_at' => date('c', $created),
            'note' => $note,
            'payment' => $payment,
        ];
    }
}
