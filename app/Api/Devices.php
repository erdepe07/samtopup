<?php

namespace App\Controllers\Api;

/** Simpan token FCM perangkat (tahap notifikasi). */
class Devices extends BaseApi
{
    public function store()
    {
        $in = $this->input();
        $tok = trim((string) ($in['token'] ?? ''));
        $plat = in_array($in['platform'] ?? '', ['android', 'ios'], true) ? $in['platform'] : 'android';
        if ($tok === '' || strlen($tok) > 255) {
            return $this->fail('Token tidak valid.');
        }
        $u = $GLOBALS['api_user'];
        $row = ['username' => $u['username'], 'fcm_token' => $tok, 'platform' => $plat, 'updated_at' => date('Y-m-d H:i:s')];
        $exists = $this->db->table('api_devices')->where('fcm_token', $tok)->countAllResults();
        if ($exists) {
            $this->db->table('api_devices')->where('fcm_token', $tok)->update($row);
        } else {
            $this->db->table('api_devices')->insert($row);
        }
        return $this->ok(null, 'Tersimpan');
    }
}
