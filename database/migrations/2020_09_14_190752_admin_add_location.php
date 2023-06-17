<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AdminAddLocation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admins', static function (Blueprint $table) {
            $table->unsignedInteger('stadium_id')->nullable();
            $table->foreign('stadium_id')->references('id')->on('stadiums');
            $table->string('phone')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('admins', static function (Blueprint $table) {
            $table->dropForeign(['stadium_id']);
            $table->dropColumn('stadium_id');
            $table->dropColumn('phone');
        });
    }
}
