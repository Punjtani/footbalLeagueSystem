<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlayerTeamTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('player_team')) {
            Schema::create('player_team', function (Blueprint $table) {
                $table->integer('player_id');
                $table->integer('team_id');
                $table->date('joined_on');
                $table->date('left_on')->nullable();
                $table->timestamps();
                $table->foreign('player_id')->references('id')->on('players');
                $table->foreign('team_id')->references('id')->on('teams');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('player_team');
    }
}
