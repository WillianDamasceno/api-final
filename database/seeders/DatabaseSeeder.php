<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'pass' => '123123123',
        ]);
        User::create([
            'name' => 'Test User',
            'email' => 'test2@example.com',
            'pass' => '123123123',
        ]);
    }
}
