<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create branches
        $branchA = Branch::create([
            'name' => 'Branch A',
            'code' => 'branch_a',
            'is_active' => true
        ]);

        $branchB = Branch::create([
            'name' => 'Branch B',
            'code' => 'branch_b',
            'is_active' => true
        ]);

        // Create Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@approx.com',
            'password' => Hash::make('password123'),
            'phone' => '9999999999',
            'branch_id' => $branchA->id,
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        // Create Lead Manager
        User::create([
            'name' => 'Lead Manager',
            'email' => 'leadmanager@approx.com',
            'password' => Hash::make('password123'),
            'phone' => '8888888888',
            'branch_id' => $branchA->id,
            'role' => 'lead_manager',
            'is_active' => true,
        ]);

        // Create Field Staff
        User::create([
            'name' => 'Field Staff',
            'email' => 'fieldstaff@approx.com',
            'password' => Hash::make('password123'),
            'phone' => '7777777777',
            'branch_id' => $branchA->id,
            'role' => 'field_staff',
            'is_active' => true,
        ]);

        // Create Reporting User
        User::create([
            'name' => 'Reporting User',
            'email' => 'reporter@approx.com',
            'password' => Hash::make('password123'),
            'phone' => '6666666666',
            'branch_id' => $branchB->id,
            'role' => 'reporting_user',
            'is_active' => true,
        ]);
    }
}
