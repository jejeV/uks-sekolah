<?php

namespace App\Main;

class SideMenu
{
    /**
     * List of side menu items.
     *
     * @return array
     */
    public static function menu()
    {
        return [
            'dashboard' => [
                'icon' => 'home',
                'route_name' => 'dashboard',
                'params' => [],
                'title' => 'Dashboard',
            ],
            'kunjungan' => [
                'icon' => 'activity',
                'route_name' => 'kunjungan.index',
                'params' => [],
                'title' => 'Kunjungan UKS',
            ],
            'pemeriksaan' => [
                'icon' => 'clipboard',
                'route_name' => 'pemeriksaan.index',
                'params' => [],
                'title' => 'Raport Kesehatan',
            ],
            'anggota' => [
                'icon' => 'users',
                'route_name' => 'anggota.index',
                'params' => [],
                'title' => 'Siswa & Guru',
            ],
        ];
    }
}
