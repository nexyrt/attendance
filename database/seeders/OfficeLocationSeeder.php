<?php

namespace Database\Seeders;

use App\Models\OfficeLocation;
use Illuminate\Database\Seeder;

class OfficeLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $officeLocations = [
            [
                'name' => 'Kantor Pusat Jakarta',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'radius' => 100, // 100 meters
                'address' => 'Jl. Sudirman No. 123, Jakarta Pusat, DKI Jakarta 10220',
            ],
            [
                'name' => 'Kantor Cabang Bandung',
                'latitude' => -6.9175,
                'longitude' => 107.6191,
                'radius' => 150, // 150 meters
                'address' => 'Jl. Asia Afrika No. 45, Bandung, Jawa Barat 40111',
            ],
            [
                'name' => 'Kantor Cabang Surabaya',
                'latitude' => -7.2575,
                'longitude' => 112.7521,
                'radius' => 120, // 120 meters
                'address' => 'Jl. Tunjungan No. 67, Surabaya, Jawa Timur 60261',
            ],
        ];

        foreach ($officeLocations as $location) {
            OfficeLocation::create($location);
        }
    }
}
