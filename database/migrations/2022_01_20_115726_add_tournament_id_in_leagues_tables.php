<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTournamentIdInLeaguesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (Schema::hasTable('leagues')) {
            Schema::table('leagues', static function (Blueprint $table) {
                if (!Schema::hasColumn('leagues', 'tournament_id')) {
                    $table->unsignedBigInteger('tournament_id')->nullable();
                }
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
        if (Schema::hasTable('leagues')) {
            Schema::table('leagues', static function (Blueprint $table) {
                if (Schema::hasColumn('leagues', 'tournament_id')) {
                    $table->dropColumn('tournament_id');
                }
            });
        }

    }
}
