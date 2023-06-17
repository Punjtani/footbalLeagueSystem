<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (! Schema::hasTable('teams')) {
            Schema::create('teams', static function (Blueprint $table) {
                $table->id();
                $table->integer('club_id')->nullable();
                $table->string('name')->nullable();
                $table->string('team_group');
                $table->integer('status')->default(0);
                $table->boolean('is_default_team')->default(false);
                $table->timestamps();
                $table->softDeletes();
                $table->string('gid', 55)->nullable()->after('id');
                //$table->foreign('club_id')->references('id')->on('clubs');
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
        Schema::dropIfExists('teams');
    }
}
