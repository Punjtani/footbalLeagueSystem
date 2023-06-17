<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnrolmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('enrolments', function (Blueprint $table) {
            $table->id();
            $table->text('user_id')->nullable();
            $table->text('curlec_method')->nullable();
            $table->text('fpx_sellerExOrderNo')->nullable();
            $table->text('fpx_sellerOrderNo')->nullable();
            $table->text('fpx_sellerId')->nullable();
            $table->text('fpx_txnCurrency')->nullable();
            $table->text('fpx_txnAmount')->nullable();
            $table->text('fpx_buyerName')->nullable();
            $table->text('fpx_buyerBankId')->nullable();
            $table->text('fpx_enrp_status')->nullable();
            $table->text('fpx_enrp_status_code')->nullable();
            $table->text('fpx_enrp_condition')->nullable();
            $table->text('fpx_enrp_allow_collection')->nullable();
            $table->text('fpx_allow_collection_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('enrolments');
    }
}
