<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStaffTeamTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('staff_team')) {
            Schema::create('staff_team', static function (Blueprint $table) {
                $table->integer('staff_id');
                $table->integer('team_id');
                $table->date('joined_on');
                $table->date('left_on')->nullable();
                $table->timestamps();
                $table->foreign('staff_id')->references('id')->on('staff');
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
        Schema::dropIfExists('staff_team');
    }
}
