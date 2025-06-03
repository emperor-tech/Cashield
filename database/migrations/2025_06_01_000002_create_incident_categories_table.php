<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('incident_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('default_severity', ['low', 'medium', 'high'])->default('medium');
            $table->enum('default_priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->integer('expected_response_time')->default(60); // in minutes
            $table->string('icon')->nullable();
            $table->string('color')->default('#3b82f6'); // Default blue color
            $table->boolean('requires_evidence')->default(false);
            $table->boolean('requires_witness')->default(false);
            $table->boolean('active')->default(true);
            $table->unsignedBigInteger('parent_id')->nullable(); // For sub-categories
            $table->timestamps();
            $table->softDeletes();

            // Foreign key for parent-child relationship (self-referential)
            $table->foreign('parent_id')
                  ->references('id')
                  ->on('incident_categories')
                  ->onDelete('set null');

            // Indexes
            $table->index('name');
            $table->index('slug');
            $table->index('default_severity');
            $table->index(['parent_id', 'active']);
        });

        // Insert some default categories
        DB::table('incident_categories')->insert([
            [
                'name' => 'Theft',
                'slug' => 'theft',
                'description' => 'Incidents involving theft of personal or university property',
                'default_severity' => 'medium',
                'default_priority' => 'medium',
                'icon' => 'fa-solid fa-shopping-bag',
                'color' => '#f59e0b',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Assault',
                'slug' => 'assault',
                'description' => 'Physical or verbal assault incidents',
                'default_severity' => 'high',
                'default_priority' => 'high',
                'icon' => 'fa-solid fa-hand-fist',
                'color' => '#ef4444',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Suspicious Activity',
                'slug' => 'suspicious-activity',
                'description' => 'Unusual or suspicious behavior that requires attention',
                'default_severity' => 'low',
                'default_priority' => 'medium',
                'icon' => 'fa-solid fa-eye',
                'color' => '#a78bfa',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Vandalism',
                'slug' => 'vandalism',
                'description' => 'Damage to campus property or facilities',
                'default_severity' => 'medium',
                'default_priority' => 'medium',
                'icon' => 'fa-solid fa-hammer',
                'color' => '#f97316',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Medical Emergency',
                'slug' => 'medical-emergency',
                'description' => 'Health-related emergencies requiring immediate attention',
                'default_severity' => 'high',
                'default_priority' => 'critical',
                'icon' => 'fa-solid fa-kit-medical',
                'color' => '#dc2626',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incident_categories');
    }
};

