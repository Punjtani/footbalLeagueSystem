<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSeasonTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (! Schema::hasTable('season_templates')) {
            Schema::create('season_templates', static function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable()->default('');
                $table->integer('type')->nullable()->default(0);
                $table->integer('number_of_teams')->nullable()->default(0);
                $table->integer('number_of_stages')->nullable()->default(0);
                $table->json('configuration')->nullable();
                $table->integer('status')->default(0);
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
        Schema::dropIfExists('season_templates');
    }
}
