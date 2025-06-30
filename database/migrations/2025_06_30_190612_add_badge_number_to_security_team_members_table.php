<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('security_team_members', function (Blueprint $table) {
            $table->string('badge_number')->nullable();
            $table->json('skills')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('security_team_members', function (Blueprint $table) {
            $table->dropColumn(['badge_number', 'skills']);
        });
    }
};
