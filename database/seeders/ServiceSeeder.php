<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run()
    {
        $services = [
            ['name' => 'Deep Cleaning', 'description' => 'Comprehensive deep cleaning service', 'price' => 150.00, 'is_active' => true],
            ['name' => 'Regular Cleaning', 'description' => 'Standard regular cleaning service', 'price' => 80.00, 'is_active' => true],
            ['name' => 'Office Cleaning', 'description' => 'Professional office cleaning', 'price' => 120.00, 'is_active' => true],
            ['name' => 'Carpet Cleaning', 'description' => 'Deep carpet cleaning and stain removal', 'price' => 100.00, 'is_active' => true],
            ['name' => 'Window Cleaning', 'description' => 'Interior and exterior window cleaning', 'price' => 70.00, 'is_active' => true],
            ['name' => 'Move In/Out Cleaning', 'description' => 'Complete move-in or move-out cleaning', 'price' => 200.00, 'is_active' => true],
            ['name' => 'Post Construction Cleaning', 'description' => 'Construction site cleanup', 'price' => 250.00, 'is_active' => true],
            ['name' => 'Upholstery Cleaning', 'description' => 'Furniture and upholstery cleaning', 'price' => 90.00, 'is_active' => true],
            ['name' => 'Kitchen Cleaning', 'description' => 'Detailed kitchen deep cleaning', 'price' => 110.00, 'is_active' => true],
            ['name' => 'Bathroom Cleaning', 'description' => 'Complete bathroom sanitization', 'price' => 60.00, 'is_active' => true],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }
    }
}
