<?php

namespace App\Main;

class SideMenu
{
    /**
     * List of side menu items.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public static function menu()
    {
        return [
            'dashboard' => [
                'icon' => 'home',
                'title' => 'Dashboard',
                'route_name' => 'dashboard',
                'params' => [
                    'layout' => 'side-menu',
                ],
                'title' => 'Dashboard'
            ],
            'Siswa & Guru' => [
                'icon' => 'users',
                'route_name' => 'anggota.index',
                'params' => [
                    'layout' => 'side-menu'
                ],
                'title' => 'Siswa & Guru'
            ],
            'Pemeriksaan' => [
                'icon' => 'edit',
                'route_name' => 'pemeriksaan.index',
                'params' => [
                    'layout' => 'side-menu'
                ],
                'title' => 'Pemeriksaan'
            ],
           
        ];
    }
}
