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
        Schema::table('reports', function (Blueprint $table) {
            $table->string('tracking_code')->nullable()->after('longitude')->comment('Tracking code for anonymous reports');
            $table->json('contact_info')->nullable()->after('tracking_code')->comment('Contact information for anonymous reporters');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('tracking_code');
            $table->dropColumn('contact_info');
        });
    }
};
