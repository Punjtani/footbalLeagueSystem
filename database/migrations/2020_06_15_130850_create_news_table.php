<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('news', static function (Blueprint $table) {
            $table->id();
            $table->string('author')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->text('content')->nullable();
            $table->string('url')->nullable();
            $table->string('image')->nullable();
            $table->string('source')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
            $table->string('gid', 55)->nullable()->after('id');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
    }
}
