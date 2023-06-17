<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('stadium_id');
            $table->unsignedInteger('stadium_facility_id');
            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');


            $table->unsignedInteger('tournament_id')->nullable();

            $table->unsignedInteger('club1_id')->nullable();
            $table->double('club1_fee')->default(0);
            $table->boolean('club1_payment_confirmed')->default(false);
            $table->string("club1_payment_proof")->nullable();


            $table->unsignedInteger('club2_id')->nullable();
            $table->double('club2_fee')->default(0);
            $table->boolean('club2_payment_confirmed')->default(false);
            $table->string("club2_payment_proof")->nullable();

            $table->unsignedInteger("contact_person_id")->nullable();


            $table->timestamps();
            $table->softDeletes();

            $table->foreign('stadium_id')
                ->references('id')
                ->on('stadiums');

            $table->foreign('stadium_facility_id')
                ->references('id')
                ->on('stadium_facilities');

            $table->foreign('tournament_id')
                ->references('id')
                ->on('tournaments');

            $table->foreign('club1_id')
                ->references('id')->on('clubs');
            $table->foreign('club2_id')
                ->references('id')->on('clubs');

            $table->foreign('contact_person_id')
                ->references('id')
                ->on('admins');

            $table->string('gid', 55)->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bookings');
    }
}
