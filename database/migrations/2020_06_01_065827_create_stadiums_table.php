<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStadiumsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('stadiums')) {
            Schema::create('stadiums', function (Blueprint $table) {
                $table->id();
//                $table->integer('tenant_id');
                $table->string('name')->nullable();
                $table->string('country')->nullable();
                $table->integer('status')->default(0);
                $table->string('location')->nullable();
                $table->string('latitude')->nullable();
                $table->string('longitude')->nullable();
                $table->integer('capacity')->nullable();
                $table->string('image')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->string('gid', 55)->nullable()->after('id');

//                $table->foreign('tenant_id')->references('id')->on('tenants');
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
        Schema::dropIfExists('stadiums');
    }
}
