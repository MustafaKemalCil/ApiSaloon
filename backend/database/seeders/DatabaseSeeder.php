<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'first_name' => 'Erol',
            'last_name' => 'YILDIZ',
            'phone' => '0000000001',
            'position' => 'Admin',
            'email' => 'test@example.com',
            'password' => Hash::make('password')
        ]);
    }
}
