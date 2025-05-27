<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emergency_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->onDelete('cascade');
            $table->foreignId('responder_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['en_route', 'on_scene', 'resolved', 'withdrawn'])->default('en_route');
            $table->integer('eta_minutes');
            $table->text('action_taken');
            $table->double('location_lat')->nullable();
            $table->double('location_lng')->nullable();
            $table->json('resources_needed')->nullable();
            $table->timestamps();
        });

        Schema::create('response_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emergency_response_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['en_route', 'on_scene', 'resolved', 'withdrawn']);
            $table->text('message');
            $table->double('location_lat')->nullable();
            $table->double('location_lng')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('response_updates');
        Schema::dropIfExists('emergency_responses');
    }
};