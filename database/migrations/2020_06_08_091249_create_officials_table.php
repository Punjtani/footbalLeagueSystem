<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOfficialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('officials')) {
            Schema::create('officials', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('country')->nullable();
                $table->string('image')->nullable();
                $table->string('type')->nullable();
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
    public function down()
    {
        Schema::dropIfExists('officials');
    }
}
