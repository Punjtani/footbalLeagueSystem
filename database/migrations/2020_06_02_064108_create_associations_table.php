<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssociationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('associations')) {
            Schema::create('associations', function (Blueprint $table) {
                $table->id();
//                $table->integer('tenant_id');
                $table->string('name')->nullable();
                $table->string('country')->nullable();
                $table->integer('status')->default(0);
                $table->string('image')->nullable();
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
    public function down()
    {
        Schema::dropIfExists('associations');
    }
}
