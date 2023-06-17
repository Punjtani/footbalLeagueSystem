<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStaffTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('staff')) {
            Schema::create('staff', static function (Blueprint $table) {
                $table->id();
//                $table->integer('tenant_id');
                $table->integer('team_id')->nullable();
                $table->string('name')->nullable();
                $table->text('description')->nullable();
                $table->string('country')->nullable();
                $table->integer('status')->default(0);
                $table->string('image')->nullable();
                $table->string('birth_place')->nullable();
                $table->string('height')->nullable();
                $table->date('dob')->nullable();
                $table->string('type')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->string('gid', 55)->nullable()->after('id');
                $table->foreign('team_id')->references('id')->on('teams');
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
        Schema::dropIfExists('staff');
    }
}
