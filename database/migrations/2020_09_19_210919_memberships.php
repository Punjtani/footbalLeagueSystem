<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class Memberships extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('membership_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image')->nullable();
            $table->string('discount_type')->nullable();
            $table->double('discount_value')->nullable();
            $table->string('gid', 55)->nullable()->after('id');
            $table->integer('status')->default(1);
            $table->integer('no_of_bookings')->nullable();
            $table->integer('weight')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('club_id');
            $table->unsignedInteger('membership_level_id');
            $table->integer('status')->default(1);
            $table->string('gid', 55)->nullable()->after('id');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('membership_level_id')->references('id')->on('clubs')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('memberships');
        Schema::dropIfExists('membership_levels');
    }
}
