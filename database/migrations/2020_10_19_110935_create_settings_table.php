<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('logo')->nullable();
                $table->string('favicon')->nullable();
                $table->string('title')->nullable();
                $table->string('url')->nullable();
                $table->text('about')->nullable();
                $table->text('address')->nullable();
                $table->text('phone')->nullable();
                $table->string('fax')->nullable();
                $table->string('email')->nullable();
                $table->string('footer')->nullable();
                $table->text('social_links')->nullable();
                $table->string('background')->nullable();
                $table->timestamps();
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
        Schema::dropIfExists('settings');
    }
}
