<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BookingDepositeAmount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->double('club1_deposit_amount')->nullable();
            $table->double('club2_deposit_amount')->nullable();
            $table->unsignedInteger('block_booking_id')->nullable();

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
            $table->dropColumn('club1_deposit_amount');
            $table->dropColumn('club2_deposit_amount');
            $table->dropColumn('block_booking_id');
        });
    }
}
