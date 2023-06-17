<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (! Schema::hasTable('sports')) {
            Schema::create('sports', static function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('sport_name');
                $table->json('scoring');
                $table->json('stats')->default(0);
                $table->json('rules');
                $table->json('roles');
                $table->json('groups');
                $table->integer('status');
                $table->timestamps();
                $table->string('gid', 55)->nullable()->after('id');
                $table->softDeletes();
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
        Schema::dropIfExists('sports');
    }
}
