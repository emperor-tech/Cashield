<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed users
        $this->call(AnonymousUserSeeder::class);
        $this->call(AdminSeeder::class);
        
        // Create test user if it doesn't exist
        if (!User::where('email', 'test@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'safety_points' => 0,
            ]);
        }
        
        // Seed campus data
        $this->call(ReportCategorySeeder::class);
        $this->call(CampusZoneSeeder::class);
    }
}
