<?php

namespace Database\Seeders;

use App\Models\SafetyPatrol;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SafetyPatrolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $user = User::where('email', 'user1@example.com')->first();
        $manager = User::where('email', 'manajer1@example.com')->first();

        SafetyPatrol::create([
            'user_id' => $user->id,
            'manager_id' => $manager->id,
            'report_date' => '2025-05-19',
            'image_path' => 'safety_patrols/test.jpg',
            'type' => 'condition',
            'description' => 'Peralatan rusak',
            'location' => 'Gudang Utama',
            'status' => 'pending_super_admin',
        ]);
    }
}
