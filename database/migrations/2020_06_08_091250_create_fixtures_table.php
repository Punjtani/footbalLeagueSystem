<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFixturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fixtures', function (Blueprint $table) {
            $table->id();
            $table->integer('match_day');
            $table->integer('season_id');
            $table->integer('team_a_id')->nullable();
            $table->integer('team_b_id')->nullable();
            $table->integer('stage_id');
            $table->integer('stadium_id')->nullable();
            $table->integer('official_id')->nullable();
            $table->date('scheduled_date')->nullable();
            $table->enum('match_status', ['upcoming', 'in_play','delayed', 'cancelled','completed' ])->default('upcoming');
            $table->enum('match_result', ['draw', 'pre_match', 'first_half', 'second_half', 'half_time', 'injury_break', 'post_match', 'team_a_win','team_b_win','upcoming'])->default('upcoming');
//            $table->json('result'); // Will use json for match_result in future
            $table->integer('team_a_score')->default(0);
            $table->integer('team_b_score')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('season_id')->references('id')->on('seasons');
            $table->foreign('team_a_id')->references('id')->on('teams');
            $table->foreign('team_b_id')->references('id')->on('teams');
            $table->foreign('stadium_id')->references('id')->on('stadiums');
            $table->foreign('stage_id')->references('id')->on('stages');
            $table->string('gid', 55)->nullable()->after('id');
            $table->foreign('official_id')->references('id')->on('officials');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fixtures');
    }
}
