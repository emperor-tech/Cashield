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
        Schema::table('campus_zones', function (Blueprint $table) {
            // Location and boundary fields
            $table->decimal('center_latitude', 10, 8)->nullable()->after('boundaries');
            $table->decimal('center_longitude', 11, 8)->nullable()->after('center_latitude');
            $table->decimal('radius', 8, 2)->nullable()->after('center_longitude');
            
            // Zone classification
            $table->string('zone_type')->default('general')->after('radius');
            $table->string('risk_level')->default('low')->after('zone_type');
            $table->string('security_level')->default('standard')->after('risk_level');
            
            // Operating hours and access
            $table->json('operating_hours')->nullable()->after('security_level');
            $table->boolean('24h_access')->default(false)->after('operating_hours');
            $table->boolean('restricted_access')->default(false)->after('24h_access');
            
            // Contact and management
            $table->string('emergency_contact')->nullable()->after('restricted_access');
            $table->string('emergency_phone')->nullable()->after('emergency_contact');
            $table->string('zone_manager')->nullable()->after('emergency_phone');
            $table->string('security_post')->nullable()->after('zone_manager');
            
            // Infrastructure
            $table->json('buildings')->nullable()->after('security_post');
            $table->json('access_points')->nullable()->after('buildings');
            $table->json('emergency_exits')->nullable()->after('access_points');
            $table->json('security_devices')->nullable()->after('emergency_exits');
            
            // Operational data
            $table->string('color')->default('#3B82F6')->after('security_devices');
            $table->integer('patrol_frequency')->default(60)->after('color'); // minutes
            $table->integer('incident_count')->default(0)->after('patrol_frequency');
            $table->timestamp('last_patrolled_at')->nullable()->after('incident_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campus_zones', function (Blueprint $table) {
            $table->dropColumn([
                'center_latitude',
                'center_longitude', 
                'radius',
                'zone_type',
                'risk_level',
                'security_level',
                'operating_hours',
                '24h_access',
                'restricted_access',
                'emergency_contact',
                'emergency_phone',
                'zone_manager',
                'security_post',
                'buildings',
                'access_points',
                'emergency_exits',
                'security_devices',
                'color',
                'patrol_frequency',
                'incident_count',
                'last_patrolled_at'
            ]);
        });
    }
};
