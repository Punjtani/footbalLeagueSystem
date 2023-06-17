<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MembershipStartAndExpiry extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('memberships');
        Schema::create('memberships',function($table){
            $table->id();
            $table->unsignedInteger('club_id');
            $table->unsignedInteger('membership_level_id');
            $table->integer('status')->default(1);
            $table->string('gid', 55)->nullable()->after('id');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('membership_level_id')->references('id')->on('membership_levels')->onDelete('CASCADE');
            $table->foreign('club_id')->references('id')->on('clubs')->onDelete('CASCADE');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
