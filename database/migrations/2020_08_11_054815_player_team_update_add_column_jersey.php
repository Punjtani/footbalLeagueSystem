<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PlayerTeamUpdateAddColumnJersey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (Schema::hasTable('player_team')) {
            Schema::table('player_team', static function (Blueprint $table) {
                if (! Schema::hasColumn('player_team', 'jersey')) {
                    $table->integer('jersey')->default(0)->nullable();
                }
                if (! Schema::hasColumn('player_team', 'playing_position')) {
                    $table->string('playing_position')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (Schema::hasTable('player_team')) {
            Schema::table('player_team', static function (Blueprint $table) {
                if (Schema::hasColumn('player_team', 'jersey')) {
                    $table->dropColumn('jersey');
                }
                if (Schema::hasColumn('player_team', 'playing_position')) {
                    $table->dropColumn('playing_position');
                }
            });
        }
    }
}
