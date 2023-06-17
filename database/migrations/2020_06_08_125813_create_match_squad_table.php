<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMatchSquadTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('match_squad', function (Blueprint $table) {
            $table->integer('match_id');
            $table->integer('player_id');
            $table->integer('team_id');
            $table->string('player_status');
            $table->integer('substituted_by')->nullable();
            $table->string('minutes_of_action')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('match_id')->references('id')->on('fixtures');
            $table->foreign('player_id')->references('id')->on('players');
            $table->foreign('substituted_by')->references('id')->on('players');
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
        Schema::dropIfExists('match_squad');
    }
}
