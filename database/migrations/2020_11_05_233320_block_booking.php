<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BlockBooking extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('block_bookings',function($table){
            $table->id();
            $table->string('name')->nullable();
            $table->string('booked_by')->nullable();
            $table->unsignedInteger('sport_id')->nullable();
            $table->unsignedInteger('tournament_id')->nullable();
            $table->unsignedInteger('contact_person_id')->nullable();

            $table->integer('status')->default(1);
            $table->string('gid', 55)->nullable()->after('id');
            $table->boolean('is_payment_collected')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

//        Schema::table('bookings', function (Blueprint $table) {
//
//        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::dropIfExists('block_bookings');
    }
}
