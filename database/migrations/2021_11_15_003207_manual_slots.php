<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ManualSlots extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('manual_slots',function($table){
            $table->id();
            $table->string('gid', 55)->nullable()->after('id');
            $table->unsignedInteger('facility_id');
            $table->date('slot_date');
            $table->time('slot_time_start');
            $table->time('slot_time_end');
            $table->double('price');
            $table->unique(['facility_id', 'slot_date', 'slot_time_start', 'slot_time_end']);
            $table->string('contacts');
            $table->string('type');
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
        Schema::dropIfExists('manual_slots');
    }
}
