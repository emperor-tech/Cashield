<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AnonymousUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create anonymous user if it doesn't exist
        if (!User::find(1)) {
            User::create([
                'id' => 1,
                'name' => 'Anonymous User',
                'email' => 'anonymous@cashield.local',
                'password' => Hash::make(Str::random(32)),
                'role' => 'anonymous',
                'safety_points' => 0
            ]);
        }
    }
}
