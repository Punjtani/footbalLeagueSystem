<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOtpEnableInSettingTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (Schema::hasTable('settings')) {
            Schema::table('settings', static function (Blueprint $table) {
                $table->boolean('is_otp_enable')->default(1);
                $table->integer('forgot_password_attempt')->default(2);
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
        if (Schema::hasTable('settings')) {
            Schema::table('settings', static function (Blueprint $table) {
                $table->dropColumn('is_otp_enable');
                $table->dropColumn('forgot_password_attempt');
            });
        }

    }
}
