<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLeagueNameToLeaguesTables extends Migration
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
                if (!Schema::hasColumn('leagues', 'league_name')) {
                    $table->longText('league_name')->nullable()->after('game_fixture_details');
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
                if (Schema::hasColumn('leagues', 'league_name')) {
                    $table->dropColumn('league_name');
                }
            });
        }

    }
}
