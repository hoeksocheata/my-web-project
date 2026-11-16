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
     *
     * គណនីសាកល្បង៖
     *   admin@example.com / password  (Admin)
     *   user@example.com  / password  (អតិថិជន)
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin', 'password' => 'password', 'role' => 'admin']
        );

        User::updateOrCreate(
            ['email' => 'user@example.com'],
            ['name' => 'Customer', 'password' => 'password', 'role' => 'user']
        );

        $this->call([BookSeeder::class, SaleSeeder::class, OrderSeeder::class]);
    }
}
