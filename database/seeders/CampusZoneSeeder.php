<?php

namespace Database\Seeders;

use App\Models\CampusZone;
use Illuminate\Database\Seeder;

class CampusZoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $zones = [
            [
                'name' => 'Academic Block',
                'code' => 'ACAD',
                'description' => 'Main academic buildings and lecture halls',
                'boundaries' => json_encode([
                    [9.0123, 7.4567],
                    [9.0124, 7.4568],
                    [9.0125, 7.4569],
                    [9.0126, 7.4570]
                ]),
                'active' => true,
            ],
            [
                'name' => 'Student Hostels',
                'code' => 'HOST',
                'description' => 'Student residential areas and dormitories',
                'boundaries' => json_encode([
                    [9.0127, 7.4571],
                    [9.0128, 7.4572],
                    [9.0129, 7.4573],
                    [9.0130, 7.4574]
                ]),
                'active' => true,
            ],
            [
                'name' => 'Sports Complex',
                'code' => 'SPRT',
                'description' => 'Sports fields, gymnasium, and recreational facilities',
                'boundaries' => json_encode([
                    [9.0131, 7.4575],
                    [9.0132, 7.4576],
                    [9.0133, 7.4577],
                    [9.0134, 7.4578]
                ]),
                'active' => true,
            ],
            [
                'name' => 'Administration Block',
                'code' => 'ADMN',
                'description' => 'Administrative offices and staff buildings',
                'boundaries' => json_encode([
                    [9.0135, 7.4579],
                    [9.0136, 7.4580],
                    [9.0137, 7.4581],
                    [9.0138, 7.4582]
                ]),
                'active' => true,
            ],
            [
                'name' => 'Library Complex',
                'code' => 'LIBR',
                'description' => 'Main library and study areas',
                'boundaries' => json_encode([
                    [9.0139, 7.4583],
                    [9.0140, 7.4584],
                    [9.0141, 7.4585],
                    [9.0142, 7.4586]
                ]),
                'active' => true,
            ],
            [
                'name' => 'Campus Gates',
                'code' => 'GATE',
                'description' => 'Main entrance and exit points',
                'boundaries' => json_encode([
                    [9.0143, 7.4587],
                    [9.0144, 7.4588],
                    [9.0145, 7.4589],
                    [9.0146, 7.4590]
                ]),
                'active' => true,
            ],
        ];

        foreach ($zones as $zone) {
            // Check if zone already exists
            if (!CampusZone::where('code', $zone['code'])->exists()) {
                CampusZone::create($zone);
            }
        }
    }
}
