<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ranks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon');
            $table->integer('required_points');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default ranks
        $ranks = [
            [
                'name' => 'Rookie Guardian',
                'icon' => '🌱',
                'required_points' => 0,
                'description' => 'Just starting your journey as a campus guardian'
            ],
            [
                'name' => 'Bronze Guardian',
                'icon' => '🥉',
                'required_points' => 100,
                'description' => 'Showing promise in campus safety'
            ],
            [
                'name' => 'Silver Guardian',
                'icon' => '🥈',
                'required_points' => 500,
                'description' => 'A reliable member of the campus safety community'
            ],
            [
                'name' => 'Gold Guardian',
                'icon' => '🥇',
                'required_points' => 1000,
                'description' => 'An experienced and trusted safety contributor'
            ],
            [
                'name' => 'Platinum Guardian',
                'icon' => '💫',
                'required_points' => 2500,
                'description' => 'A distinguished member of the campus safety network'
            ],
            [
                'name' => 'Diamond Guardian',
                'icon' => '💎',
                'required_points' => 5000,
                'description' => 'An elite campus safety expert'
            ],
            [
                'name' => 'Legendary Guardian',
                'icon' => '👑',
                'required_points' => 10000,
                'description' => 'A legendary figure in campus safety'
            ],
        ];

        foreach ($ranks as $rank) {
            DB::table('ranks')->insert($rank);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ranks');
    }
};