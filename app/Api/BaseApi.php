<?php

namespace App\Controllers\Api;

use App\Filters\ApiAuth;
use App\Models\M_Base;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Induk semua endpoint API mobile. Sengaja TIDAK memakai BaseController web
 * (tidak ada session, tidak ada redirect/flashdata).
 */
abstract class BaseApi extends Controller
{
    protected $db;
    protected $M_Base;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);
        $this->db = \Config\Database::connect();
        $this->M_Base = new M_Base();
    }

    protected function ok($data = null, string $msg = 'OK'): ResponseInterface
    {
        return $this->response->setStatusCode(200)->setJSON(['success' => true, 'message' => $msg, 'data' => $data]);
    }

    protected function fail(string $msg, int $code = 422): ResponseInterface
    {
        return $this->response->setStatusCode($code)->setJSON(['success' => false, 'message' => $msg]);
    }

    /** Body JSON (atau form) sebagai array. */
    protected function input(): array
    {
        $j = $this->request->getJSON(true);
        return is_array($j) ? $j : (array) $this->request->getPost();
    }

    /** Pengguna dari token, atau null bila tidak login (login opsional). */
    protected function user(): ?array
    {
        return $GLOBALS['api_user'] ?? ApiAuth::resolve($this->request);
    }

    /** Pembatas laju per IP: maksimal $max permintaan per $seconds detik. */
    protected function throttle(string $bucket, int $max, int $seconds): bool
    {
        $key = $bucket . '_' . md5($this->request->getIPAddress());
        return service('throttler')->check($key, $max, $seconds);
    }

    protected function clean($v): string
    {
        return trim(strip_tags((string) $v));
    }

    protected function img(string $dir, ?string $file): string
    {
        return $file ? base_url('assets/images/' . $dir . '/' . $file) : '';
    }

    /** Level -> harga produk, sama persis dengan aturan web. */
    protected function priceFor(array $product, ?array $user): int
    {
        $p = (int) $product['price'];
        if ($user) {
            if ($user['level'] === 'Silver' && (int) $product['price_silver'] >= 500) {
                $p = (int) $product['price_silver'];
            } elseif ($user['level'] === 'Gold' && (int) $product['price_gold'] >= 500) {
                $p = (int) $product['price_gold'];
            }
        }
        return $p;
    }

    protected function mapStatus(string $s): string
    {
        switch ($s) {
            case 'Pending': return 'pending';
            case 'Processing': return 'processing';
            case 'Success':
            case 'Finished': return 'success';
            case 'Expired': return 'expired';
            default: return 'failed'; // Canceled
        }
    }

    /** Kode metode untuk app: kode Tripay bila ada, kalau tidak "m{id}". */
    protected function methodCode(array $m): string
    {
        return $m['code'] !== '' ? $m['code'] : 'm' . $m['id'];
    }

    protected function findMethod(string $code): ?array
    {
        $b = $this->db->table('method')->where('status', 'On');
        if (preg_match('/^m(\d+)$/', $code, $x)) {
            $b->where('id', (int) $x[1]);
        } else {
            $b->where('code', $code);
        }
        return $b->get()->getRowArray() ?: null;
    }

    /** Game yang didukung app: hanya form standar (ID + zona opsional). */
    protected function gameSupported(array $g): bool
    {
        return $g['status'] === 'On' && $g['target'] === 'default';
    }

    protected function needsZone(array $g): bool
    {
        return in_array($g['check_code'], ['mobilelegends', 'mobilelegend'], true);
    }
}
