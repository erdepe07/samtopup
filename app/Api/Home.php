<?php

namespace App\Controllers\Api;

class Home extends BaseApi
{
    public function index()
    {
        $banners = [];
        foreach ($this->M_Base->all_data('banner', 5) as $b) {
            $banners[] = ['image' => $this->img('banner', $b['image'])];
        }

        $games = $this->db->table('games')->where('status', 'On')->where('target', 'default')
            ->orderBy('sort', 'ASC')->get()->getResultArray();

        $categories = [];
        foreach ($this->M_Base->all_data_order('category', 'sort') as $c) {
            $list = [];
            foreach ($games as $g) {
                if ($g['category'] === $c['category']) {
                    $list[] = [
                        'slug' => $g['slug'],
                        'name' => $g['games'],
                        'image' => $this->img('games', $g['image']),
                    ];
                }
            }
            if ($list) {
                $categories[] = ['name' => $c['category'], 'games' => $list];
            }
        }

        $methods = [];
        foreach ($this->M_Base->data_where('method', 'status', 'On') as $m) {
            $methods[] = ['image' => $this->img('method', $m['image'])];
        }

        return $this->ok([
            'banners' => $banners,
            'categories' => $categories,
            'methods' => $methods,
        ]);
    }
}
