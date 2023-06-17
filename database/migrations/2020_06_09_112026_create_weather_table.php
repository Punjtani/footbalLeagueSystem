<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWeatherTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('weather')) {
            Schema::create('weather', function (Blueprint $table) {
                $table->id();
                $table->integer('fixture_id')->nullable();
                $table->float('temp')->nullable();
                $table->string('unit')->nullable();
                $table->float('temp_feels_like')->nullable();
                $table->integer('rain')->nullable();
                $table->integer('wind')->nullable();
                $table->string('weather_type', 50)->nullable();
                $table->float('rain_amount')->nullable();
                $table->string('direction', 50)->nullable();
                $table->integer('cloud')->nullable();
                $table->integer('relative_humidity')->nullable();
                $table->integer('ultra_violet')->nullable();
                $table->integer('gust')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->string('gid', 55)->nullable()->after('id');
                $table->foreign('fixture_id')->references('id')->on('fixtures');

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
        Schema::dropIfExists('weather');
    }
}
