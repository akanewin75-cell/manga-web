<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'akanewin75@gmail.com'],
            [
                'name' => 'Akane Admin',
                'password' => Hash::make('akane.123'),
                'role' => 'admin',
            ]
        );
    }
}
