<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // User::create([
        //     'name' => 'Kepala',
        //     'email' => 'kepala@example.com',
        //     'password' => Hash::make('password'),
        //     'role' => 'super_admin',
        // ]);
        // User::create([
        //     'name' => 'Ketua',
        //     'email' => 'ketua@example.com',
        //     'password' => Hash::make('password'),
        //     'role' => 'super_admin',
        // ]);
        User::create([
            'name' => 'Manajer1',
            'email' => 'manajer1@example.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
        ]);
    }
}
