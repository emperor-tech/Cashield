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
        // Create campus zones table
        Schema::create('campus_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->json('boundaries')->nullable(); // For storing polygon coordinates
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Create security teams table
        Schema::create('security_teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('leader_id')->constrained('users');
            $table->foreignId('zone_id')->constrained('campus_zones');
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Create security team members pivot table
        Schema::create('security_team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('security_team_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('role')->default('member'); // member, supervisor, leader
            $table->timestamp('joined_at');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['security_team_id', 'user_id']);
        });

        // Create security shifts table
        Schema::create('security_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('team_id')->constrained('security_teams');
            $table->foreignId('zone_id')->constrained('campus_zones');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('route_data')->nullable(); // For storing patrol route data
            $table->timestamps();
        });

        // Create shift incidents table
        Schema::create('shift_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_id')->constrained('security_shifts')->onDelete('cascade');
            $table->string('type');
            $table->text('description');
            $table->string('severity')->default('low');
            $table->json('location')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('reported_at');
            $table->timestamps();
        });

        // Create zone checkpoints table
        Schema::create('zone_checkpoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->constrained('campus_zones')->onDelete('cascade');
            $table->string('name');
            $table->string('code')->unique();
            $table->json('location');
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Create checkpoint scans table
        Schema::create('checkpoint_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_id')->constrained('security_shifts')->onDelete('cascade');
            $table->foreignId('checkpoint_id')->constrained('zone_checkpoints')->onDelete('cascade');
            $table->timestamp('scanned_at');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkpoint_scans');
        Schema::dropIfExists('zone_checkpoints');
        Schema::dropIfExists('shift_incidents');
        Schema::dropIfExists('security_shifts');
        Schema::dropIfExists('security_team_members');
        Schema::dropIfExists('security_teams');
        Schema::dropIfExists('campus_zones');
    }
}; 