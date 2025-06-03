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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('campus');
            $table->string('location');
            $table->text('description');
            $table->enum('severity', ['low', 'medium', 'high'])->default('low');
            $table->enum('status', ['open', 'in_progress', 'resolved'])->default('open');
            $table->boolean('anonymous')->default(false);
            $table->string('media_path')->nullable();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_team_id')->nullable()->constrained('security_teams')->nullOnDelete();
            $table->foreignId('zone_id')->nullable()->constrained('campus_zones')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->integer('response_time')->nullable(); // in minutes
            $table->integer('resolution_time')->nullable(); // in minutes
            $table->json('details')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('severity');
            $table->index('status');
            $table->index('created_at');
            $table->index(['status', 'severity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
}; 