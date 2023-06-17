<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePointsTableDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('points_table_data')) {
            Schema::create('points_table_data', function (Blueprint $table) {
                $table->id();
                $table->integer('team_id');
                $table->integer('tournament_id');
                $table->json('team_points')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->foreign('team_id')->references('id')->on('teams');
                $table->string('gid', 55)->nullable()->after('id');
                $table->foreign('tournament_id')->references('id')->on('tournaments');
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
        Schema::dropIfExists('points_table_data');
    }
}
