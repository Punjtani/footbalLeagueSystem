<?php

namespace App\Http\Controllers\FrontEnd;

use App\Http\Controllers\Controller;
use App\Collection;
use App\Enrolment;
use App\TransactionHistory;
use App\Traits\BelongsToTenant;
use Illuminate\Http\Request;

class SubscriptionCallbackController extends Controller
{

    public function merchantUrl(Request $request)
    {
        $this->transactionHistory($request);
        return redirect()->route('user.subscriptions');
    }

    public function merchantCallback(Request $request)
    {
        $this->transactionHistory($request);
        return response()->json(['data' => []], 200);
    }

    public function transactionHistory($request)
    {
        if (!is_null($request->curlec_method)) {
            TransactionHistory::updateOrCreate([
                "fpx_fpxTxnId" => $request->fpx_fpxTxnId,
                "fpx_sellerOrderNo" => $request->fpx_sellerOrderNo,
            ], [
                "curlec_method" => $request->curlec_method,
                "fpx_fpxTxnId" => $request->fpx_fpxTxnId,
                "fpx_sellerExOrderNo" => $request->fpx_sellerExOrderNo,
                "fpx_fpxTxnTime" => $request->fpx_fpxTxnTime,
                "fpx_sellerOrderNo" => $request->fpx_sellerOrderNo,
                "fpx_sellerId" => $request->fpx_sellerId,
                "fpx_txnCurrency" => $request->fpx_txnCurrency,
                "fpx_txnAmount" => $request->fpx_txnAmount,
                "fpx_buyerName" => $request->fpx_buyerName,
                "fpx_buyerBankId" => $request->fpx_buyerBankId,
                "fpx_debitAuthCode" => $request->fpx_debitAuthCode,
                "fpx_type" => $request->fpx_type,
                "fpx_notes" => $request->fpx_notes,
            ]);
        }

    }


    public function enrolmentCallback(Request $request)
    {
        if (!is_null($request->curlec_method)) {
            Enrolment::updateOrCreate([
                "fpx_sellerOrderNo" => $request->fpx_sellerOrderNo,
            ], [
                "curlec_method" => $request->curlec_method,
                "fpx_sellerExOrderNo" => $request->fpx_sellerExOrderNo,
                "fpx_sellerOrderNo" => $request->fpx_sellerOrderNo,
                "fpx_sellerId" => $request->fpx_sellerId,
                "fpx_txnCurrency" => $request->fpx_txnCurrency,
                "fpx_txnAmount" => $request->fpx_txnAmount,
                "fpx_buyerName" => $request->fpx_buyerName,
                "fpx_buyerBankId" => $request->fpx_buyerBankId,
                "fpx_enrp_status" => $request->fpx_enrp_status,
                "fpx_enrp_status_code" => $request->fpx_enrp_status_code,
                "fpx_enrp_condition" => $request->fpx_enrp_condition,
                "fpx_enrp_allow_collection" => $request->fpx_enrp_allow_collection,
                "fpx_allow_collection_date" => $request->fpx_allow_collection_date,
            ]);
        }
        return response()->json(['data' => []], 200);
    }

    public function collectionCallback(Request $request)
    {

        if (!is_null($request->curlec_method)) {
            Collection::updateOrCreate([
                "fpx_sellerOrderNo" => $request->fpx_sellerOrderNo,
            ], [
                "curlec_method" => $request->curlec_method,
                "fpx_sellerExOrderNo" => $request->fpx_sellerExOrderNo,
                "fpx_sellerOrderNo" => $request->fpx_sellerOrderNo,
                "fpx_sellerId" => $request->fpx_sellerId,
                "fpx_txnCurrency" => $request->fpx_txnCurrency,
                "fpx_txnAmount" => $request->fpx_txnAmount,
                "fpx_buyerName" => $request->fpx_buyerName,
                "fpx_buyerBankId" => $request->fpx_buyerBankId,
                "fpx_collectionBatchId" => $request->fpx_collectionBatchId,
                "fpx_collectionStatus" => $request->fpx_collectionStatus,
                "fpx_collectionStatusDesc" => $request->fpx_collectionStatusDesc,
                "fpx_collectionReference" => $request->fpx_collectionReference,
                "fpx_collectionDescription" => $request->fpx_collectionDescription,
                "fpx_collectionNotes" => $request->fpx_collectionNotes,
                "cc_transaction_id" => $request->cc_transaction_id,
                "cc_authorisation_code" => $request->cc_authorisation_code,
                "invoice_number" => $request->invoice_number,
            ]);
        }
        return response()->json(['data' => []], 200);
    }


}
