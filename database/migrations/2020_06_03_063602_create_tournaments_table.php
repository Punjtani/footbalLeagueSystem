<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTournamentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (! Schema::hasTable('tournaments')) {
            Schema::create('tournaments', static function (Blueprint $table) {
                $table->id();
//                $table->integer('tenant_id');
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('team_group');
                $table->integer('status')->default(0);
                $table->integer('occurrence')->default(1);
                $table->integer('association_id')->nullable();
                $table->date('since')->nullable();
                $table->string('image')->nullable();
                $table->timestamps();
                $table->softDeletes();
//                $table->foreign('tenant_id')->references('id')->on('tenants');
                $table->string('gid', 55)->nullable()->after('id');
                $table->foreign('association_id')->references('id')->on('associations');
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
        Schema::dropIfExists('tournaments');
    }
}
