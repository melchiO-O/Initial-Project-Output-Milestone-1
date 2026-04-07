<?php

namespace Database\Seeders;

use App\Models\Car;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin account
        User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@fastlane.com',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        // Regular user account
        User::create([
            'name'     => 'Regular User',
            'email'    => 'user@fastlane.com',
            'password' => Hash::make('password'),
            'role'     => 'user',
        ]);

        // Sample cars
        $cars = [
            ['brand' => 'Toyota',    'model' => 'Vios',     'plate_number' => 'ABC 1234', 'year' => 2022, 'price_per_day' => 1500, 'status' => 'available', 'description' => 'Fuel-efficient sedan, perfect for city driving.'],
            ['brand' => 'Honda',     'model' => 'Civic',    'plate_number' => 'DEF 5678', 'year' => 2023, 'price_per_day' => 2000, 'status' => 'available', 'description' => 'Sleek and sporty, great for long trips.'],
            ['brand' => 'Ford',      'model' => 'Ranger',   'plate_number' => 'GHI 9012', 'year' => 2021, 'price_per_day' => 2500, 'status' => 'rented',    'description' => 'Powerful pickup truck for rough terrain.'],
            ['brand' => 'Mitsubishi','model' => 'Montero',  'plate_number' => 'JKL 3456', 'year' => 2022, 'price_per_day' => 3000, 'status' => 'available', 'description' => 'Premium SUV with 4x4 capability.'],
            ['brand' => 'Hyundai',   'model' => 'Tucson',   'plate_number' => 'MNO 7890', 'year' => 2023, 'price_per_day' => 2200, 'status' => 'available', 'description' => 'Modern crossover with advanced safety features.'],
            ['brand' => 'Nissan',    'model' => 'Navara',   'plate_number' => 'PQR 1122', 'year' => 2020, 'price_per_day' => 1800, 'status' => 'rented',    'description' => 'Reliable workhorse for business and leisure.'],
        ];

        foreach ($cars as $car) {
            Car::create($car);
        }
    }
}