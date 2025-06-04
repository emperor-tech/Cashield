<?php

namespace Database\Seeders;

use App\Models\ReportCategory;
use Illuminate\Database\Seeder;

class ReportCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Theft',
                'description' => 'Theft of personal property or university assets',
                'severity_level' => 'medium',
                'response_time' => 30,
                'requires_approval' => false,
            ],
            [
                'name' => 'Assault',
                'description' => 'Physical or verbal assault on campus',
                'severity_level' => 'high',
                'response_time' => 15,
                'requires_approval' => false,
            ],
            [
                'name' => 'Suspicious Activity',
                'description' => 'Suspicious persons or activities on campus',
                'severity_level' => 'low',
                'response_time' => 45,
                'requires_approval' => true,
            ],
            [
                'name' => 'Vandalism',
                'description' => 'Damage to university property or facilities',
                'severity_level' => 'medium',
                'response_time' => 60,
                'requires_approval' => true,
            ],
            [
                'name' => 'Medical Emergency',
                'description' => 'Medical emergencies requiring immediate attention',
                'severity_level' => 'high',
                'response_time' => 5,
                'requires_approval' => false,
            ],
            [
                'name' => 'Fire',
                'description' => 'Fire or smoke in campus buildings',
                'severity_level' => 'high',
                'response_time' => 5,
                'requires_approval' => false,
            ],
            [
                'name' => 'Harassment',
                'description' => 'Harassment, bullying, or threatening behavior',
                'severity_level' => 'high',
                'response_time' => 20,
                'requires_approval' => true,
            ],
        ];

        foreach ($categories as $category) {
            // Check if category already exists
            if (!ReportCategory::where('name', $category['name'])->exists()) {
                ReportCategory::create($category);
            }
        }
    }
}
