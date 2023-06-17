<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (! Schema::hasTable('tenants')) {
            Schema::create('tenants', static function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->integer('status')->default(0);
                $table->string('api_token')->nullable();
                $table->unsignedBigInteger('sport_id');
                $table->timestamps();
                $table->softDeletes();
                $table->string('gid', 55)->nullable()->after('id');
                //$table->foreign('sport_id')->references('id')->on('sports');
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
        Schema::dropIfExists('tenants');
    }
}
