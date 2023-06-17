<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlayerSeasonTeamTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('player_season_team', function (Blueprint $table) {
            $table->integer('season_id');
            $table->integer('player_id')->nullable();
            $table->integer('team_id');
            $table->integer('group')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('season_id')->references('id')->on('seasons');
            $table->foreign('player_id')->references('id')->on('players');
            $table->foreign('team_id')->references('id')->on('teams');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('player_season_team');
    }
}
