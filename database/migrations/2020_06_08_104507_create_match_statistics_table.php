<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMatchStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('match_statistics', function (Blueprint $table) {
            $table->id();
            $table->integer('match_id');
            $table->integer('team_id');
            $table->string('stat_key');
            $table->integer('player_id');
            $table->integer('minute_of_action');
            $table->json('stat_value');
            $table->boolean('is_own_goal')->default(0);
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('team_id')->references('id')->on('teams');
            $table->foreign('match_id')->references('id')->on('fixtures');
            $table->string('gid', 55)->nullable()->after('id');
            $table->foreign('player_id')->references('id')->on('players');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('match_statistics');
    }
}
