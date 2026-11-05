<?php

namespace App\Libraries;

/**
 * Cek nickname game. Meniru logika web (Games::order/get-detail) tetapi
 * kunci & alamat penyedia dibaca dari .env, BUKAN ditulis di kode:
 *
 *   NICK_BASE_URL=https://alamat-penyedia-cek-nickname
 *   NICK_KEY=kunci-penyedia
 *   APIGAMES_MERCHANT=Mxxxxxxxxxxxx
 *   APIGAMES_SIGNATURE=signature-cek-username
 */
class NicknameChecker
{
    public function check(string $code, string $uid, string $zone): ?string
    {
        $base = rtrim((string) env('NICK_BASE_URL', ''), '/');
        $key = (string) env('NICK_KEY', '');

        if ($base !== '' && $key !== '') {
            $q = ['key' => $key];
            if (in_array($code, ['mobilelegends', 'mobilelegend'], true)) {
                $path = '/api/game/mobile-legends/';
                $q = ['id' => $uid, 'zone' => $zone] + $q;
            } elseif (in_array($code, ['freefire', 'free-fire'], true)) {
                $path = '/api/game/free-fire/';
                $q = ['id' => $uid] + $q;
            } elseif (in_array($code, ['higgsdomino', 'higgs'], true)) {
                $path = '/api/game/higgsdomino/';
                $q = ['id' => $uid] + $q;
            } else {
                $path = '/api/game/' . rawurlencode($code) . '/';
                $q = ['id' => $uid] + $q;
            }
            $r = $this->get($base . $path . '?' . http_build_query($q));
            if (is_array($r) && !isset($r['error_msg'])) {
                $n = $r['data']['username'] ?? $r['nickname'] ?? null;
                if (is_string($n) && $n !== '') {
                    return $n;
                }
            }
        }

        // Cadangan: ApiGames cek-username (hanya game yang dikenal)
        $map = ['mobilelegends' => 'mobilelegend', 'mobilelegend' => 'mobilelegend',
            'freefire' => 'freefire', 'free-fire' => 'freefire',
            'higgsdomino' => 'higgs', 'higgs' => 'higgs'];
        $merchant = (string) env('APIGAMES_MERCHANT', '');
        $sig = (string) env('APIGAMES_SIGNATURE', '');
        if (isset($map[$code]) && $merchant !== '' && $sig !== '') {
            $target = ($zone !== '' && $zone !== '1') ? $uid . $zone : $uid;
            $url = 'https://v1.apigames.id/merchant/' . rawurlencode($merchant) . '/cek-username/'
                . $map[$code] . '?' . http_build_query(['user_id' => $target, 'signature' => $sig]);
            $r = $this->get($url);
            if (is_array($r) && ($r['status'] ?? 0) == 1 && !empty($r['data']['username'])) {
                return (string) $r['data']['username'];
            }
        }
        return null;
    }

    private function get(string $url): ?array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_TIMEOUT => 12,
            CURLOPT_CONNECTTIMEOUT => 6,
        ]);
        $out = curl_exec($ch);
        curl_close($ch);
        $j = json_decode((string) $out, true);
        return is_array($j) ? $j : null;
    }
}
