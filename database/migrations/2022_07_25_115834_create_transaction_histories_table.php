<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_histories', function (Blueprint $table) {
            $table->id();
            $table->text('user_id')->nullable();
            $table->text('curlec_method')->nullable();
            $table->text('fpx_fpxTxnId')->nullable();
            $table->text('fpx_sellerExOrderNo')->nullable();
            $table->text('fpx_fpxTxnTime')->nullable();
            $table->text('fpx_sellerOrderNo')->nullable();
            $table->text('fpx_sellerId')->nullable();
            $table->text('fpx_txnCurrency')->nullable();
            $table->text('fpx_txnAmount')->nullable();
            $table->text('fpx_buyerName')->nullable();
            $table->text('fpx_buyerBankId')->nullable();
            $table->text('fpx_debitAuthCode')->nullable();
            $table->text('fpx_type')->nullable();
            $table->text('fpx_notes')->nullable();
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
        Schema::dropIfExists('transaction_histories');
    }
}
