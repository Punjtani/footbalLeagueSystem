<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class LocationsFacilities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('stadium_facilities')) {
            Schema::create('stadium_facilities', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->unsignedInteger('sport_id');
                $table->unsignedInteger('stadium_id');
                $table->foreign('sport_id')->references('id')->on('sports');
                $table->foreign('stadium_id')->references('id')->on('stadiums')->onDelete('cascade');
                $table->unsignedInteger('status')->default(1);
                $table->timestamps();
                $table->softDeletes();
                $table->string('gid', 55)->nullable()->after('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::dropIfExists('stadium_facilities');
    }
}
