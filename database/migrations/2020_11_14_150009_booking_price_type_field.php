<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BookingPriceTypeField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('fee_type')->default("per_team");
            $table->index(['fee_type']);
            $table->double('slot_fee')->nullable();
            $table->double('slot_deposit')->nullable();
            $table->boolean('slot_fee_paid')->nullable();
            $table->boolean('slot_fee_deposit_paid')->nullable();
            $table->boolean('is_receipt_gen')->nullable();
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
            $table->dropColumn('is_receipt_gen');
            $table->dropColumn('slot_deposit');
            $table->dropColumn('slot_fee_deposit_paid');
            $table->dropColumn('slot_fee_paid');
            $table->dropColumn('slot_fee');
            $table->dropIndex(['fee_type']);
            $table->dropColumn('fee_type');
        });
    }
}
