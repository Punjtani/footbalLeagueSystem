<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClubPlayerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('club_player', function (Blueprint $table) {
            $table->integer('player_id');
            $table->integer('club_id')->nullable();
            $table->date('joined_on')->nullable();
            $table->date('left_on')->nullable();
            $table->timestamps();
            $table->foreign('player_id')->references('id')->on('players');
            //$table->foreign('club_id')->references('id')->on('clubs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('club_player');
    }
}
