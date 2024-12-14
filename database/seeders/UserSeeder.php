<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create Admin User
        User::create([
            'name' => 'Super Admin',
            'email' => 'super@admin.com',
            'phone_number' => '1234567890',
            'password' => Hash::make('password'),
            'role' => 'Supplier',
            'address' => '123 Admin Street',
            'compliance_document' => null,
        ]);

        // Create Sample Users for each role
        $roles = ['Supplier', 'Manufacturer', 'Distributor', 'Pharmacy', 'Patient'];
        
        foreach ($roles as $role) {
            User::create([
                'name' => $role . ' User',
                'email' => strtolower($role) . '@example.com',
                'phone_number' => '1234567890',
                'password' => Hash::make('password'),
                'role' => $role,
                'address' => '123 ' . $role . ' Street',
                'compliance_document' => null,
            ]);
        }

        // Create additional random users
        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'name' => 'Test User ' . $i,
                'email' => 'user' . $i . '@example.com',
                'phone_number' => '123456789' . $i,
                'password' => Hash::make('password'),
                'role' => $roles[array_rand($roles)],
                'address' => '123 Test Street ' . $i,
                'compliance_document' => null,
            ]);
        }
    }
}