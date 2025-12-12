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
                'name' => 'Main Office',
                'latitude' => -0.45028193,
                'longitude' => 117.14613855,
                'radius' => 100, // 100 meters
                'address' => 'Perumahan Pondok Alam Indah Blok D No. 3, RT.002/RW.005, Kec. Sempaja, Kota Samarinda, Kalimantan Timur 75243',
            ],
        ];

        foreach ($officeLocations as $location) {
            OfficeLocation::create($location);
        }
    }
}
