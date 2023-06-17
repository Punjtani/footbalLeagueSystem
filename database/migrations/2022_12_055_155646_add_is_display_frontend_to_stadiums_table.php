<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsDisplayFrontendToStadiumsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stadiums', function (Blueprint $table) {
            $table->boolean('is_display_frontend')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stadiums', function (Blueprint $table) {
            $table->dropColumn('is_display_frontend');
        });
    }
}
