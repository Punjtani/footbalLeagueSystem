<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlayersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (! Schema::hasTable('players')) {
            Schema::create('players', static function (Blueprint $table) {
                $table->id();
//                $table->integer('tenant_id');
                $table->string('name')->nullable();
                $table->string('country')->nullable();
                $table->integer('status')->default(0);
                $table->string('image')->nullable();
                $table->string('birth_place')->nullable();
                $table->string('height')->nullable();
                $table->date('dob')->nullable();
                $table->integer('jersey')->nullable();
                $table->string('playing_position')->nullable();
                $table->string('twitter')->nullable();
                $table->string('facebook')->nullable();
                $table->string('instagram')->nullable();
                $table->string('youtube')->nullable();
                $table->timestamps();
                $table->string('gid', 55)->nullable()->after('id');
                $table->softDeletes();
//                $table->foreign('tenant_id')->references('id')->on('tenants');
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
        Schema::dropIfExists('players');
    }
}
