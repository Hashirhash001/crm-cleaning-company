<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            // Cleaning Services
            ['name' => 'Deep Cleaning Full House', 'service_type' => 'cleaning', 'is_active' => true],
            ['name' => 'Sofa Shampoo', 'service_type' => 'cleaning', 'is_active' => true],
            ['name' => 'Bathroom Deep', 'service_type' => 'cleaning', 'is_active' => true],
            ['name' => 'Floor Scrubbing', 'service_type' => 'cleaning', 'is_active' => true],
            ['name' => 'Kitchen Deep', 'service_type' => 'cleaning', 'is_active' => true],
            ['name' => 'Pressure Wash', 'service_type' => 'cleaning', 'is_active' => true],

            // Pest Control Services
            ['name' => 'Termite Treatment', 'service_type' => 'pest_control', 'is_active' => true],
            ['name' => 'General Pest', 'service_type' => 'pest_control', 'is_active' => true],
        ];

        foreach ($services as $service) {
            DB::table('services')->insert([
                'name' => $service['name'],
                'service_type' => $service['service_type'],
                'description' => null,
                'price' => null,
                'is_active' => $service['is_active'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
