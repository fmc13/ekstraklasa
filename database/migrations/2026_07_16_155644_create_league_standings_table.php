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
        Schema::create('league_standings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('league_id')->index();
            $table->unsignedSmallInteger('season')->index();
            $table->unsignedInteger('api_team_id');
            $table->unsignedTinyInteger('rank');
            $table->string('team_name');
            $table->string('team_logo')->nullable();
            $table->unsignedSmallInteger('points')->default(0);
            $table->smallInteger('goals_diff')->default(0);
            $table->string('group_name')->default('');
            $table->string('form', 16)->nullable();
            $table->string('status', 32)->nullable();
            $table->string('description')->nullable();
            $table->unsignedTinyInteger('played')->default(0);
            $table->unsignedTinyInteger('win')->default(0);
            $table->unsignedTinyInteger('draw')->default(0);
            $table->unsignedTinyInteger('lose')->default(0);
            $table->unsignedSmallInteger('goals_for')->default(0);
            $table->unsignedSmallInteger('goals_against')->default(0);
            $table->timestamp('api_updated_at')->nullable();
            $table->timestamps();

            $table->unique(['league_id', 'season', 'api_team_id', 'group_name']);
            $table->index(['league_id', 'season', 'rank']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('league_standings');
    }
};
