<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // First, ensure the columns exist
        if (!Schema::hasColumn('users', 'username')) {
            echo "Error: username column doesn't exist. Run migrations first!\n";
            return;
        }

        // Create admin user
        User::updateOrCreate(
            ['username' => 'Admin'],
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('Admin123'),
                'role' => 'admin',
                'department' => 'Tech',
                'status' => 'active',
            ]
        );

        echo "Admin user created successfully!\n";
        echo "Username: Admin\n";
        echo "Password: Admin123\n";
    }
}