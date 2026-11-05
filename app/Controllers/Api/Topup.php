<?php

namespace App\Controllers\Api;

/**
 * Isi saldo dari aplikasi. Sama dengan User::topup di web:
 * buat transaksi Tripay -> baris tabel `topup` (Pending).
 * Saldo ditambahkan oleh callback Tripay web yang sudah ada (Sistem::callback),
 * jadi tidak ada logika kredit saldo baru di sini.
 */
class Topup extends BaseApi
{
    private const MIN = 10000;
    private const MAX = 5000000;

    /** Metode yang bisa dipakai isi saldo + batas nominal. */
    public function methods()
    {
        $list = [];
        foreach ($this->M_Base->data_where('method', 'status', 'On') as $m) {
            if ($m['provider'] !== 'Tripay') {
                continue;
            }
            $list[] = [
                'code' => $this->methodCode($m),
                'name' => $m['method'],
                'image' => $this->img('method', $m['image']),
                'group' => $m['type'],
                'fee_flat' => 0,
                'fee_percent' => 0,
                'requires_login' => true,
            ];
        }
        return $this->ok(['min' => self::MIN, 'max' => self::MAX, 'methods' => $list]);
    }

    public function create()
    {
        if (!$this->throttle('topup', 10, 60)) {
            return $this->fail('Terlalu banyak permintaan, coba lagi sebentar.', 429);
        }
        $u = $GLOBALS['api_user'];
        $in = $this->input();
        $amount = (int) ($in['amount'] ?? 0);
        $mcode = $this->clean($in['method_code'] ?? '');

        if ($amount < self::MIN) {
            return $this->fail('Isi saldo minimal Rp ' . number_format(self::MIN, 0, ',', '.'));
        }
        if ($amount > self::MAX) {
            return $this->fail('Isi saldo maksimal Rp ' . number_format(self::MAX, 0, ',', '.'));
        }
        $method = $this->findMethod($mcode);
        if (!$method || $method['provider'] !== 'Tripay') {
            return $this->fail('Metode pembayaran tidak tersedia.', 404);
        }

        $dupe = $this->db->table('topup')->where('username', $u['username'])->where('method_id', $method['id'])
            ->where('amount', $amount)->where('status', 'Pending')
            ->where('date_create >=', date('Y-m-d H:i:s', time() - 30))->countAllResults();
        if ($dupe > 0) {
            return $this->fail('Permintaan yang sama baru saja dibuat. Cek riwayat isi saldo.', 409);
        }

        $uniq = $method['uniq'] === 'Y' ? random_int(0, 999) : 0;
        $total = $amount + $uniq;

        do {
            $id = date('Ymd') . random_int(100000, 999999);
        } while ($this->db->table('topup')->where('topup_id', $id)->countAllResults() > 0);

        $r = $this->tripayCreate($method['code'], $id, $total, $u);
        if (!is_array($r) || empty($r['success'])) {
            return $this->fail('Pembayaran gagal dibuat: ' . ($r['message'] ?? 'tidak dapat terhubung ke Tripay'), 502);
        }
        $d = $r['data'];
        $code = !empty($d['qr_url']) ? $d['qr_url'] : (!empty($d['pay_code']) ? $d['pay_code'] : ($d['checkout_url'] ?? ''));
        if ($code === '') {
            return $this->fail('Pembayaran gagal dibuat.', 502);
        }

        $this->db->table('topup')->insert([
            'topup_id' => $id,
            'username' => $u['username'],
            'method_id' => $method['id'],
            'method' => $method['method'],
            'amount' => $total,
            'status' => 'Pending',
            'payment_code' => $code,
            'date_create' => date('Y-m-d H:i:s'),
            'payment_type' => $method['type'],
            'method_code' => $method['code'],
            'payment_gateway' => 'Tripay',
        ]);

        $row = $this->db->table('topup')->where('topup_id', $id)->get()->getRowArray();
        return $this->ok($this->format($row), 'Permintaan isi saldo dibuat');
    }

    public function show($id = '')
    {
        if (!preg_match('/^\d{14}$/', (string) $id)) {
            return $this->fail('Data tidak ditemukan.', 404);
        }
        $u = $GLOBALS['api_user'];
        $row = $this->db->table('topup')->where('topup_id', $id)->where('username', $u['username'])->get()->getRowArray();
        if (!$row) {
            return $this->fail('Data tidak ditemukan.', 404);
        }
        return $this->ok($this->format($row));
    }

    public function mine()
    {
        $u = $GLOBALS['api_user'];
        $rows = $this->db->table('topup')->where('username', $u['username'])->orderBy('id', 'DESC')->limit(50)->get()->getResultArray();
        return $this->ok(['orders' => array_map([$this, 'format'], $rows)]);
    }

    private function tripayCreate(string $code, string $id, int $amount, array $u): ?array
    {
        $data = [
            'method' => $code,
            'merchant_ref' => $id,
            'amount' => $amount,
            'customer_name' => $u['username'],
            'customer_email' => 'email@domain.com',
            'customer_phone' => $u['wa'],
            'order_items' => [['sku' => 'DS', 'name' => 'Topup Saldo', 'price' => $amount, 'quantity' => 1]],
            'return_url' => base_url(),
            'expired_time' => time() + 86400,
            'signature' => hash_hmac('sha256', $this->M_Base->u_get('tripay-merchant') . $id . $amount, $this->M_Base->u_get('tripay-private')),
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

    /** Dibentuk seperti objek Order agar layar pembayaran di app bisa dipakai ulang. */
    private function format(array $t): array
    {
        $created = strtotime($t['date_create']);
        $st = $t['status'] === 'Success' ? 'success' : ($t['status'] === 'Canceled' ? 'failed' : 'pending');
        if ($st === 'pending' && time() > $created + 86400) {
            $st = 'expired';
        }

        $payment = null;
        if ($st === 'pending') {
            $code = (string) $t['payment_code'];
            $isUrl = preg_match('#^https?://#', $code) === 1;
            $isQr = $isUrl && strpos($code, '/qr/') !== false;
            $payment = [
                'type' => $isQr ? 'qris' : ($isUrl ? 'redirect' : (($t['payment_type'] ?? '') === 'Convenience Store' ? 'retail' : 'va')),
                'checkout_url' => ($isUrl && !$isQr) ? $code : '',
                'qr_url' => $isQr ? $code : '',
                'pay_code' => $isUrl ? '' : $code,
                'pay_label' => $isUrl ? '' : $t['method'],
                'expired_at' => date('c', $created + 86400),
            ];
        }

        return [
            'invoice' => $t['topup_id'],
            'status' => $st,
            'game' => 'Isi Saldo',
            'product' => 'Isi Saldo',
            'target' => $t['username'],
            'method' => $t['method'],
            'total' => (int) $t['amount'],
            'created_at' => date('c', $created),
            'note' => $st === 'success' ? 'Saldo sudah masuk ke akun Anda.' : ($st === 'failed' ? 'Isi saldo dibatalkan.' : ''),
            'payment' => $payment,
        ];
    }
}
