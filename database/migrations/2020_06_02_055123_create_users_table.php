<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (! Schema::hasTable('admins')) {
            Schema::create('admins', static function (Blueprint $table) {
                $table->id();
                $table->integer('tenant_id')->nullable();
                $table->string('name')->nullable();
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('role')->default(Config::get('app.ROLE_ADMIN'));
                $table->integer('status')->default(0);
                $table->string('password');
                $table->string('login_token')->nullable();
                $table->boolean('isDefault')->default(0);
                $table->rememberToken();
                $table->timestamps();
                $table->softDeletes();
                $table->string('gid', 55)->nullable()->after('id');
                $table->foreign('tenant_id')->references('id')->on('tenants');
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
        Schema::dropIfExists('users');
    }
}
