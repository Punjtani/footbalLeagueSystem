<?php

namespace App\Http\Controllers;


use App\Helpers\Helper;
use App\Helpers\HtmlTemplatesHelper;
use App\Subscription;
use App\SubscriptionHistory;
use App\SubscriptionTrackingHistory;
use App\User;
use DaveJamesMiller\Breadcrumbs\Facades\Breadcrumbs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubscriptionController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function activateSubscription(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'subscription_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        } else {
            Subscription::updateOrCreate([
                'subscription_id' => $request->subscription_id,
            ], [
                'user_id' => $request->user_id,
                'email' => $request->email,
                'subscription_id' => $request->subscription_id,
                'status' => 'pending'
            ]);

            SubscriptionTrackingHistory::create([
                'user_id' => $request->user_id,
                'username' => auth()->user()->name,
                'email' => $request->email,
                'subscription_id' => $request->subscription_id,
                'status' => 'pending'
            ]);
            return $this->successResponse(200, 'Subscribed successfully.');

        }
    }

    public function deactivateSubscription(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subscription_id' => 'required',
            'user_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        } else {
            $user = User::whereId($request->user_id)->first();
            if ($request->type == 'cancel') {
                Subscription::whereUserId($request->user_id)->whereSubscriptionId($request->subscription_id)->update(['status' => 'inactive']);
                $status = 'inactive';
            } else {
                Subscription::whereUserId($request->user_id)->whereSubscriptionId($request->subscription_id)->update(['status' => 'pending']);
                $status = 'pending';
            }

            SubscriptionTrackingHistory::create([
                'user_id' => $request->user_id,
                'username' => auth()->user()->name,
                'email' => $user->email,
                'subscription_id' => $request->subscription_id,
                'status' => $status
            ]);

            return redirect()->back()
                ->with([
                    'flash_status' => 'success',
                    'flash_message' => 'Subscription deactivated successfully.'
                ]);

        }
    }

    public function successResponse($status, $message)
    {
        return response()->json(
            [
                "status" => $status,
                "msg" => $message,
            ]);
    }

    public function errorResponse($status, $message)
    {
        return response()->json(
            [
                "status" => $status,
                "msg" => $message,
            ]);
    }


    public function index()
    {


        if (request()->ajax()) {
            return datatables(Subscription::filters(request())->select('subscriptions.*'))->addColumn('actions', static function ($data) {
                return HtmlTemplatesHelper::get_action_dropdown($data, '', false, true, auth()->user()->can('Subscription.Create'), false,false,false);
            })->addColumn('status', static function ($data) {
                return ucfirst($data->status);
            })->addColumn('payment_status', static function ($data) {
                return $data->is_first_of_month == true ? 'Payment Pending' : 'Payment Successfull';
            })->rawColumns(['actions', 'email', 'status', 'payment_status'])->make(true);
        }

        return view('pages.subscriptions.list', ['add_url' => route("subscriptions.create"), 'filterData' => $this->advance_filters(), 'breadcrumbs' => Breadcrumbs::generate('subscriptions')]);
    }

    private function advance_filters(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $subscription = Subscription::query()->findOrFail($id);
        $trackingHistories = SubscriptionTrackingHistory::whereUserId($subscription->user_id)->with('user')->get();
        return view('pages.subscriptions.add', ['title' => 'Edit Subscription', 'item' => $subscription, 'trackingHistories' => $trackingHistories, 'breadcrumbs' => Breadcrumbs::generate('subscriptions.edit', $subscription)]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
//        dd($request->all());
        $subscription = Subscription::query()->findOrFail($id);
        $user = User::whereId($subscription->user_id)->first();
        $oldStatus = $subscription->status;
        $subscription->status = $request->status;
        $subscription->is_first_of_month = false;
        $subscription->save();
        if ($request->status == 'active') {

            if ($request->save_button === 'activate') {
                $amount = 499;
            } else {
                $historyCount = SubscriptionHistory::whereUserId($subscription->user_id)->count();
                if ($historyCount >= 1) {
                    if ($oldStatus == 'inactive' && $request->status == 'active') {
                        $amount = 499;
                    } else {
                        $amount = 199;
                    }
                } else {
                    $amount = 499;
                }
            }

            SubscriptionHistory::create([
                'user_id' => $subscription->user_id,
                'subscription_date' => date('Y-m-d'),
                'amount' => $amount,
            ]);

        }

        SubscriptionTrackingHistory::create([
            'user_id' => $subscription->user_id,
            'username' => auth()->user()->name,
            'email' => $user->email,
            'subscription_id' => $subscription->subscription_id,
            'status' => $request->status,
        ]);
        return Helper::jsonMessage($subscription !== null, Subscription::INDEX_URL, $subscription !== null ? 'Record Successfully Updated' : 'Unable to Update');
    }

}
