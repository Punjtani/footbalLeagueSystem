<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCollectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('collections', function (Blueprint $table) {
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
            $table->text('fpx_collectionBatchId')->nullable();
            $table->text('fpx_collectionStatus')->nullable();
            $table->text('fpx_collectionStatusDesc')->nullable();
            $table->text('fpx_collectionReference')->nullable();
            $table->text('fpx_collectionDescription')->nullable();
            $table->text('fpx_collectionNotes')->nullable();
            $table->text('cc_transaction_id')->nullable();
            $table->text('cc_authorisation_code')->nullable();
            $table->text('invoice_number')->nullable();
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
        Schema::dropIfExists('collections');
    }
}
