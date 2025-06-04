<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder {
    public function run() {
        // Check if admin user already exists
        if (!User::where('email', 'admin@cashield.ng')->exists()) {
            User::create([
                'name' => 'Campus Admin',
                'email' => 'admin@cashield.ng',
                'password' => Hash::make('securepass'),
                'role' => 'admin',
                'safety_points' => 0,
            ]);
        }
    }
}
