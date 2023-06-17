<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueNameConstraintInClubsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (Schema::hasTable('clubs')) {
            Schema::table('clubs', static function (Blueprint $table) {
                $table->unique('name');
                $table->text('jersey_color')->nullable();
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
        if (Schema::hasTable('clubs')) {
            Schema::table('clubs', static function (Blueprint $table) {
                $table->dropColumn('jersey_color');
            });
        }

    }
}
