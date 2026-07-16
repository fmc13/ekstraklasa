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
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('league_id')->index();
            $table->unsignedSmallInteger('season')->index();
            $table->unsignedInteger('api_team_id');
            $table->string('name');
            $table->string('code', 16)->nullable();
            $table->string('country')->nullable();
            $table->unsignedSmallInteger('founded')->nullable();
            $table->boolean('national')->default(false);
            $table->string('logo')->nullable();
            $table->unsignedInteger('api_venue_id')->nullable();
            $table->string('venue_name')->nullable();
            $table->string('venue_address')->nullable();
            $table->string('venue_city')->nullable();
            $table->unsignedInteger('venue_capacity')->nullable();
            $table->string('venue_surface')->nullable();
            $table->string('venue_image')->nullable();
            $table->timestamps();

            $table->unique(['league_id', 'season', 'api_team_id']);
            $table->index(['league_id', 'season']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
