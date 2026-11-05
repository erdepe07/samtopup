<?php

namespace App\Controllers\Api;

use App\Libraries\NicknameChecker;

class Games extends BaseApi
{
    private function game(string $slug): ?array
    {
        $g = $this->db->table('games')->where('slug', $slug)->get()->getRowArray();
        return ($g && $this->gameSupported($g)) ? $g : null;
    }

    public function show($slug = '')
    {
        $g = $this->game($slug);
        if (!$g) {
            return $this->fail('Game tidak ditemukan atau belum tersedia di aplikasi.', 404);
        }
        $user = $this->user();

        $fields = [['key' => 'user_id', 'label' => 'User ID', 'hint' => 'Masukkan User ID / Nomor Tujuan', 'type' => $g['category'] === 'Games' ? 'number' : 'phone']];
        if ($this->needsZone($g)) {
            $fields[] = ['key' => 'zone_id', 'label' => 'Zone ID', 'hint' => 'Contoh: 1234', 'type' => 'number'];
        }

        $rows = $this->db->table('product')->where('games_id', $g['id'])->where('status', 'On')
            ->orderBy('sort', 'ASC')->get()->getResultArray();

        $groups = [];
        foreach ($rows as $p) {
            $name = 'Produk';
            if ((int) $p['category_id'] > 0) {
                $c = $this->M_Base->data_where('product_category', 'id', $p['category_id']);
                if (count($c) === 1) {
                    $name = $c[0]['category'] ?? $c[0]['name'] ?? $c[0]['title'] ?? 'Produk';
                }
            }
            $price = $this->priceFor($p, $user);
            $groups[$name][] = [
                'id' => (string) $p['id'],
                'name' => $p['product'],
                'price' => $price,
                'original_price' => (int) $p['price'] > $price ? (int) $p['price'] : null,
                'note' => '',
            ];
        }
        $groupsOut = [];
        foreach ($groups as $n => $items) {
            $groupsOut[] = ['name' => $n, 'products' => $items];
        }

        $methods = [];
        foreach ($this->M_Base->data_where('method', 'status', 'On') as $m) {
            if ($m['provider'] !== 'Tripay') {
                continue; // app: hanya Tripay + saldo (Manual butuh upload bukti di web)
            }
            $methods[] = [
                'code' => $this->methodCode($m),
                'name' => $m['method'],
                'image' => $this->img('method', $m['image']),
                'group' => $m['type'],
                'fee_flat' => 0,
                'fee_percent' => 0,
                'requires_login' => false,
            ];
        }
        if ($this->M_Base->u_get('pay_balance') === 'Y') {
            $methods[] = ['code' => 'balance', 'name' => 'Saldo Akun', 'image' => '', 'group' => 'Saldo',
                'fee_flat' => 0, 'fee_percent' => 0, 'requires_login' => true];
        }

        return $this->ok([
            'slug' => $g['slug'],
            'name' => $g['games'],
            'image' => $this->img('games', $g['image']),
            'description' => trim(strip_tags(html_entity_decode((string) $g['content']))),
            'supports_nickname' => $g['check_status'] === 'Y',
            'fields' => $fields,
            'groups' => $groupsOut,
            'methods' => $methods,
        ]);
    }

    public function checkNickname($slug = '')
    {
        if (!$this->throttle('nick', 20, 60)) {
            return $this->fail('Terlalu banyak permintaan, coba lagi sebentar.', 429);
        }
        $g = $this->game($slug);
        if (!$g || $g['check_status'] !== 'Y') {
            return $this->fail('Cek nickname tidak tersedia untuk game ini.', 404);
        }
        $f = $this->input()['fields'] ?? [];
        $uid = $this->clean($f['user_id'] ?? '');
        $zone = $this->clean($f['zone_id'] ?? '');
        if ($uid === '' || ($this->needsZone($g) && $zone === '')) {
            return $this->fail('ID Player harus diisi.');
        }
        $nick = (new NicknameChecker())->check($g['check_code'], $uid, $zone);
        if ($nick === null) {
            return $this->fail('Nickname tidak ditemukan. Periksa kembali ID Anda.', 404);
        }
        return $this->ok(['nickname' => $nick]);
    }
}
