<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateBookingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->float('club2_fee')->nullable()->change();
            $table->float('club1_fee')->nullable()->change();
            $table->boolean('match_referee_required')->nullable();
            $table->boolean('club1_water_boxes')->nullable();
            $table->boolean('club2_water_boxes')->nullable();

            $table->boolean('club1_fully_paid')->nullable();
            $table->boolean('club2_fully_paid')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('club2_fully_paid');
            $table->dropColumn('club1_fully_paid');
            $table->dropColumn('club2_water_boxes');
            $table->dropColumn('club1_water_boxes');
            $table->dropColumn('match_referee_required');
        });
    }
}
