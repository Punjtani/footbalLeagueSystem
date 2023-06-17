<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSeasonSponsorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('season_sponsor', function (Blueprint $table) {
            $table->integer('sponsor_id');
            $table->integer('season_id');
            $table->integer('team_id')->nullable();
            $table->string('sponsor_type');
            $table->date('sponsorship_start_date');
            $table->date('sponsorship_end_date');
            $table->foreign('sponsor_id')->references('id')->on('sponsors');
            $table->foreign('season_id')->references('id')->on('seasons');
            $table->foreign('team_id')->references('id')->on('teams');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('season_sponsor');
    }
}
