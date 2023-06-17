<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (! Schema::hasTable('stages')) {
            Schema::create('stages', static function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->integer('season_id');
                $table->integer('stage_number');
                $table->integer('type');
                $table->json('configuration')->nullable();
                $table->string('gid', 55)->nullable()->after('id');
                $table->timestamps();
                $table->foreign('season_id')->references('id')->on('seasons');
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
        Schema::dropIfExists('stages');
    }
}
