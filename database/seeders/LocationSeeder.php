<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Location::create([
            'name' => 'Politeknik Elektronika Negeri Surabaya',
            'youtube_url' => 'https://youtu.be/4clpg00y098?si=EBYdGZY3EeNKJF7k',

        ]);
    }
}
