<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSeasonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (! Schema::hasTable('seasons')) {
            Schema::create('seasons', static function (Blueprint $table) {
                $table->id();
                $table->integer('tournament_id');
                $table->integer('season_template_id');
                $table->string('name');
                $table->string('image')->nullable();
                $table->integer('status')->default(0);
                $table->timestamps();
                $table->softDeletes();
                $table->string('gid', 55)->nullable()->after('id');
               // $table->foreign('tournament_id')->references('id')->on('tournaments');
                $table->foreign('season_template_id')->references('id')->on('season_templates');
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
        Schema::dropIfExists('seasons');
    }
}
