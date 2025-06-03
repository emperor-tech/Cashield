<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder {
    public function run() {
        User::create([
            'name' => 'Campus Admin',
            'email' => 'admin@cashield.ng',
            'password' => Hash::make('securepass'),
            'role' => 'admin',
            'safety_points' => 0,
        ]);
    }
}
