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
        Schema::create('fixtures', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('league_id')->index();
            $table->unsignedSmallInteger('season')->index();
            $table->unsignedInteger('api_fixture_id');
            $table->string('round')->index();
            $table->unsignedSmallInteger('round_number')->nullable()->index();
            $table->timestamp('kickoff_at')->nullable()->index();
            $table->string('status_short', 16)->nullable();
            $table->string('status_long')->nullable();
            $table->unsignedInteger('home_team_id');
            $table->string('home_team_name');
            $table->string('home_team_logo')->nullable();
            $table->unsignedInteger('away_team_id');
            $table->string('away_team_name');
            $table->string('away_team_logo')->nullable();
            $table->smallInteger('home_goals')->nullable();
            $table->smallInteger('away_goals')->nullable();
            $table->string('venue_name')->nullable();
            $table->string('venue_city')->nullable();
            $table->timestamps();

            $table->unique(['league_id', 'season', 'api_fixture_id']);
            $table->index(['league_id', 'season', 'round_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fixtures');
    }
};
