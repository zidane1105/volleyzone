<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin VolleyZone',
            'email' => 'admin@volleyzone.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'User VolleyZone',
            'email' => 'user@volleyzone.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        \App\Models\Court::create([
            'name' => 'Lapangan Utama (Indoor)',
            'description' => 'Lapangan voli indoor standar internasional dengan permukaan taraflex.',
            'facilities' => ['Tribun', 'Kamar Ganti', 'AC', 'Papan Skor Digital'],
            'price_per_hour' => 150000.00,
            'is_active' => true,
        ]);

        \App\Models\Court::create([
            'name' => 'Lapangan B (Outdoor)',
            'description' => 'Lapangan voli outdoor dengan lantai plester berstandar. Cocok untuk latihan santai dan pertandingan persahabatan.',
            'facilities' => ['Tribun Mini', 'Kamar Ganti', 'Penerangan Malam'],
            'price_per_hour' => 80000.00,
            'is_active' => true,
        ]);

        \App\Models\Court::create([
            'name' => 'Lapangan C (Semi-Indoor)',
            'description' => 'Lapangan beratap dengan sirkulasi udara alami. Nyaman dipakai bermain tanpa takut kehujanan.',
            'facilities' => ['Kamar Ganti', 'Kantin', 'Parkir Luas', 'Papan Skor'],
            'price_per_hour' => 120000.00,
            'is_active' => true,
        ]);
    }
}
