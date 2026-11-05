<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Filter 'apiauth': cek header "Authorization: Bearer <token>".
 * Hasilnya disimpan di $GLOBALS['api_user'] (baris tabel users).
 */
class ApiAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $user = self::resolve($request);
        if ($user === null) {
            return service('response')->setStatusCode(401)->setJSON([
                'success' => false,
                'message' => 'Sesi berakhir, silakan login lagi.',
            ]);
        }
        $GLOBALS['api_user'] = $user;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }

    /** Dipakai juga oleh endpoint yang login-nya opsional. */
    public static function resolve(RequestInterface $request): ?array
    {
        $h = $request->getHeaderLine('Authorization');
        if (!preg_match('/^Bearer\s+([A-Za-z0-9]{40,128})$/', $h, $m)) {
            return null;
        }
        $db = \Config\Database::connect();
        $hash = hash('sha256', $m[1]);
        $row = $db->table('api_tokens')->where('token_hash', $hash)
            ->where('expires_at >', date('Y-m-d H:i:s'))->get()->getRowArray();
        if (!$row) {
            return null;
        }
        $user = $db->table('users')->where('username', $row['username'])->where('status', 'On')
            ->get()->getRowArray();
        if (!$user) {
            return null;
        }
        $db->table('api_tokens')->where('id', $row['id'])->update(['last_used' => date('Y-m-d H:i:s')]);
        return $user;
    }
}
