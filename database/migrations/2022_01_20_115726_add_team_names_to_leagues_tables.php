<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTeamNamesToLeaguesTables extends Migration
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
                if (!Schema::hasColumn('leagues', 'team_names')) {
                    $table->longText('team_names')->nullable()->after('result_matrix');
                }
                if (!Schema::hasColumn('leagues', 'game_fixture_details')) {
                    $table->longText('game_fixture_details')->nullable()->after('team_names');
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
                if (Schema::hasColumn('leagues', 'team_names')) {
                    $table->dropColumn('team_names');
                }
                if (Schema::hasColumn('leagues', 'game_fixture_details')) {
                    $table->dropColumn('game_fixture_details');
                }
            });
        }

    }
}
