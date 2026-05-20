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
        User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@fastlane.com',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        User::create([
            'name'           => 'Regular User',
            'email'          => 'user@fastlane.com',
            'password'       => Hash::make('password'),
            'role'           => 'user',
            'license_number' => 'N01-23-456789',
            'license_expiry' => '2027-12-31',
        ]);

        $cars = [
            ['brand' => 'Toyota',    'model' => 'Vios',    'plate_number' => 'ABC 1234', 'year' => 2022, 'price_per_day' => 1500, 'status' => 'available', 'seat_capacity' => 5, 'description' => 'Fuel-efficient sedan, perfect for city driving.', 'image_url' => 'https://img.philkotse.com/2021/11/23/WFFKkBCT/img-5155-a0a7_wm.jpg'],
            ['brand' => 'Honda',     'model' => 'Civic',   'plate_number' => 'DEF 5678', 'year' => 2023, 'price_per_day' => 2000, 'status' => 'available', 'seat_capacity' => 5, 'description' => 'Sleek and sporty, great for long trips.', 'image_url' => 'https://img.philkotse.com/temp/2024/07/26/honda-civic-type-r-1-1-4336-wm-3cb0.webp'],
            ['brand' => 'Ford',      'model' => 'Ranger',  'plate_number' => 'GHI 9012', 'year' => 2021, 'price_per_day' => 2500, 'status' => 'available', 'seat_capacity' => 5, 'description' => 'Powerful pickup truck for rough terrain.', 'image_url' => 'https://di-sitebuilder-assets.dealerinspire.com/Ford/MLP/Ranger/cactus+gray.jpg'],
            ['brand' => 'Mitsubishi','model' => 'Montero', 'plate_number' => 'JKL 3456', 'year' => 2022, 'price_per_day' => 3000, 'status' => 'available', 'seat_capacity' => 7, 'description' => 'Premium SUV with 4x4 capability.', 'image_url' => 'https://images.topgear.com.ph/topgear/images/2022/01/04/2022-mitsubishi-montero-sport-black-series-13-1641258835.jpg'],
            ['brand' => 'Hyundai',   'model' => 'Tucson',  'plate_number' => 'MNO 7890', 'year' => 2023, 'price_per_day' => 2200, 'status' => 'available', 'seat_capacity' => 5, 'description' => 'Modern compact SUV with advanced features.', 'image_url' => 'https://media.drive.com.au/obj/tx_q:50,rs:auto:1920:1080:1/driveau/upload/cms/uploads/y4nywzzeultrjgqbdqdk'],
            ['brand' => 'Nissan',    'model' => 'Navara',  'plate_number' => 'PQR 1122', 'year' => 2021, 'price_per_day' => 2700, 'status' => 'available', 'seat_capacity' => 5, 'description' => 'Rugged pickup with great towing capacity.', 'image_url' => 'https://d1hv7ee95zft1i.cloudfront.net/custom/blog-post-photo/gallery/2021-nissan-navara-pro-4x-philippines-front-quarter-60cc76d7aa723.jpg'],
            ];

        foreach ($cars as $car) {
            Car::create($car);
        }
    }
}