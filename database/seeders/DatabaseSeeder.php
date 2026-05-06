<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Jenjang;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Users
        User::insert([
            [
                'name'       => 'Admin UKS',
                'email'      => 'admin@sekolah.sch.id',
                'password'   => Hash::make('admin123'),
                'role'       => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Petugas UKS',
                'email'      => 'petugas@sekolah.sch.id',
                'password'   => Hash::make('petugas123'),
                'role'       => 'petugas',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Jenjang
        Jenjang::insert([
            ['nama' => 'TK', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'SD', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'SMP', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
