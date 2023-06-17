<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MatchesTabe extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger("booking_id")->nullable();
            $table->foreign("booking_id")->references("id")->on("bookings")->onDelete('restrict');
            $table->double('team_1_score')->nullable()->default(0);
            $table->double('team_2_score')->nullable()->default(0);
            $table->enum('match_status', ['upcoming', 'in_play','delayed', 'cancelled','completed' ])->default('upcoming');
            $table->enum('match_result', ['draw', 'team_1_win','team_2_win','upcoming'])->default('upcoming');
            $table->timestamps();
            $table->softDeletes();
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
        Schema::dropIfExists('matches');
    }
}
