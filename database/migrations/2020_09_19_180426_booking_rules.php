<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BookingRules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('booking_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('weekly_schedule')->nullable();
            $table->integer('booking_window_duration')->default(1);
            $table->string('gid', 55)->nullable()->after('id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('booking_rules');
    }
}
