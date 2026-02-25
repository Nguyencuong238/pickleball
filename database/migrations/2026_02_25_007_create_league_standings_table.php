<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('league_standings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_id')->constrained()->cascadeOnDelete();
            $table->foreignId('league_team_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('played')->default(0);
            $table->unsignedInteger('wins')->default(0);
            $table->unsignedInteger('losses')->default(0);
            $table->unsignedInteger('draws')->default(0);
            $table->unsignedInteger('games_won')->default(0);
            $table->unsignedInteger('games_lost')->default(0);
            $table->integer('points')->default(0);
            $table->unsignedInteger('rank')->default(0);
            $table->timestamps();

            $table->unique(['league_id', 'league_team_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('league_standings');
    }
};
