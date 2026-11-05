<?php

namespace App\Controllers\Api;

class Auth extends BaseApi
{
    private function userOut(array $u): array
    {
        return ['name' => $u['username'], 'username' => $u['username'], 'balance' => (int) $u['balance']];
    }

    private function issueToken(string $username): string
    {
        $token = bin2hex(random_bytes(32));
        $this->db->table('api_tokens')->insert([
            'token_hash' => hash('sha256', $token),
            'username' => $username,
            'created_at' => date('Y-m-d H:i:s'),
            'expires_at' => date('Y-m-d H:i:s', time() + 90 * 86400),
        ]);
        return $token;
    }

    public function login()
    {
        if (!$this->throttle('login', 10, 60)) {
            return $this->fail('Terlalu banyak percobaan, coba lagi sebentar.', 429);
        }
        $in = $this->input();
        $username = trim((string) ($in['username'] ?? ''));
        $pass = trim((string) ($in['password'] ?? ''));
        if ($username === '' || $pass === '') {
            return $this->fail('Username dan password harus diisi.');
        }
        $u = $this->db->table('users')->where('username', $username)->get()->getRowArray();

        // Web menyimpan hash dari password yang sudah di-addslashes+htmlspecialchars;
        // coba kedua bentuk agar akun web & app saling cocok.
        $webForm = addslashes(trim(htmlspecialchars($pass)));
        $valid = $u && (password_verify($pass, $u['password']) || password_verify($webForm, $u['password']));
        if (!$valid) {
            return $this->fail('Username atau password salah.', 401);
        }
        if ($u['status'] !== 'On') {
            return $this->fail('Akun kamu telah dinonaktifkan.', 403);
        }
        return $this->ok(['token' => $this->issueToken($u['username']), 'user' => $this->userOut($u)]);
    }

    public function register()
    {
        if (!$this->throttle('register', 5, 600)) {
            return $this->fail('Terlalu banyak percobaan, coba lagi nanti.', 429);
        }
        $in = $this->input();
        $username = trim((string) ($in['username'] ?? ''));
        $pass = trim((string) ($in['password'] ?? ''));
        $wa = preg_replace('/\D/', '', (string) ($in['whatsapp'] ?? ''));

        if (isset($in['password_confirm']) && trim((string) $in['password_confirm']) !== $pass) {
            return $this->fail('Konfirmasi password tidak sesuai.');
        }
        if (!preg_match('/^[A-Za-z0-9_.]{3,30}$/', $username)) {
            return $this->fail('Username 3-30 karakter: huruf, angka, titik, garis bawah.');
        }
        if (strlen($pass) < 6) {
            return $this->fail('Password minimal 6 karakter.');
        }
        if (strlen($pass) > 24) {
            return $this->fail('Password maksimal 24 karakter.');
        }
        if (strlen($wa) < 10 || strlen($wa) > 14 || substr($wa, 0, 2) !== '08') {
            return $this->fail('No. WhatsApp harus diawali 08 (10-14 digit).');
        }
        if ($this->db->table('users')->where('username', $username)->countAllResults() > 0) {
            return $this->fail('Username sudah digunakan.', 409);
        }

        $this->db->table('users')->insert([
            'username' => $username,
            // bentuk yang sama dengan pendaftaran web, supaya bisa login di web juga
            'password' => password_hash(addslashes(trim(htmlspecialchars($pass))), PASSWORD_DEFAULT),
            'balance' => 0,
            'wa' => $wa,
            'ref' => $this->M_Base->random_string(5),
            'ref_by' => '',
            'level' => 'Member',
            'status' => 'On',
            'date_create' => date('Y-m-d H:i:s'),
        ]);
        $u = $this->db->table('users')->where('username', $username)->get()->getRowArray();
        return $this->ok(['token' => $this->issueToken($username), 'user' => $this->userOut($u)], 'Pendaftaran berhasil');
    }

    public function me()
    {
        return $this->ok($this->userOut($GLOBALS['api_user']));
    }

    public function logout()
    {
        if (preg_match('/^Bearer\s+(\S+)$/', $this->request->getHeaderLine('Authorization'), $m)) {
            $this->db->table('api_tokens')->where('token_hash', hash('sha256', $m[1]))->delete();
        }
        return $this->ok(null, 'Logout berhasil');
    }
}
