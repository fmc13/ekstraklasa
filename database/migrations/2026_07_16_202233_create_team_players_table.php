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
        Schema::create('team_players', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('league_id')->index();
            $table->unsignedSmallInteger('season')->index();
            $table->unsignedInteger('api_team_id')->index();
            $table->unsignedInteger('api_player_id');
            $table->string('name');
            $table->unsignedTinyInteger('age')->nullable();
            $table->unsignedTinyInteger('number')->nullable();
            $table->string('position')->nullable();
            $table->string('photo')->nullable();
            $table->timestamps();

            $table->unique(['league_id', 'season', 'api_team_id', 'api_player_id']);
            $table->index(['api_team_id', 'season']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_players');
    }
};
