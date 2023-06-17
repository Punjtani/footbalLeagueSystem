<?php

namespace App\Http\Controllers;

use App\BookingRule;
use App\User;
use Spatie\Permission\Models\Role;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use DaveJamesMiller\Breadcrumbs\Facades\Breadcrumbs;
use App\Helpers\HtmlTemplatesHelper;

class BookingRuleController extends BaseController
{

    public function index()
    {
        if (request()->ajax()) {
            return datatables(BookingRule::filters(request()))->addColumn('actions', static function($data){
                return HtmlTemplatesHelper::get_action_dropdown($data, '', false,false,auth()->user()->can('BookingRules.Update'),auth()->user()->can('BookingRules.Delete'),false,false);
            })->rawColumns(['actions'])->make(true);
        }
        return view('pages.booking-rules.list', ['add_url' => route("booking-rules.create"), 'breadcrumbs' => Breadcrumbs::generate('booking-rules')]);
    }

    /**
     * @inheritDoc
     */
    public function create()
    {
        $admins = User::where('status',1)->pluck('name','id');
        return view('pages.booking-rules.add', ['title' => 'Add Booking Rules', 'admins'=> $admins]);
    }

    public function store(Request $request)
    {
        $this->validate($request,BookingRule::$validation);

        $bookingRule = new BookingRule();
        $bookingRule->fill($request->all());
        $bookingRule->save();

        return Helper::jsonMessage($bookingRule->id !== null, BookingRule::INDEX_URL);
    }

     /**
     * @inheritDoc
     */
    public function edit($id)
    {
        $admins = User::where('status',1)->pluck('name','id');
        $item = BookingRule::find($id);
        return view('pages.booking-rules.add', ['title' => 'Edit Booking Rules', 'admins'=> $admins, 'item'=> $item]);
    }

    public function update($id,Request $request)
    {
        $this->validate($request,BookingRule::$validation);

        $bookingRule = BookingRule::findOrFail($id);
        $bookingRule->fill($request->all());
        $bookingRule->save();

        return Helper::jsonMessage($bookingRule->id !== null, BookingRule::INDEX_URL);
    }

        /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id)
    {

        $bookingRule =  BookingRule::query()->findOrFail($id);
        if($bookingRule->stadiumFacilities()->count() > 0){
            $stadiumFacilities = [];
            foreach($bookingRule->stadiumFacilities as $sf){
                $stadiumFacilities[] = $sf->stadium->name .": ".$sf->title;
            }
            return Helper::jsonMessage(false, NULL,  "Can't delete, Rule is being used in (".join(", ",$stadiumFacilities).")");
        }
        try {
            $bookingRule->delete();
            return Helper::jsonMessage(true, NULL, 'Record Successfully deleted');
        } catch (\Exception $e) {
            return Helper::jsonMessage(false, NULL,  $e->getMessage());
        }
    }
}
