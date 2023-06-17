<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FacilityLocationBookingRule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stadium_facilities', function (Blueprint $table) {
            $table->unsignedInteger('booking_rule_id')->nullable();
            $table->foreign('booking_rule_id')->references('id')->on('booking_rules');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stadium_facilities', function (Blueprint $table) {
            $table->dropForeign(['booking_rule_id']);
            $table->dropColumn('booking_rule_id');
        });
    }
}
