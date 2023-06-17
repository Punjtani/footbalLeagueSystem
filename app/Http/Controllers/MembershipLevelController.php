<?php

namespace App\Http\Controllers;


use App\Helpers\Helper;
use Illuminate\Http\Request;
use DaveJamesMiller\Breadcrumbs\Facades\Breadcrumbs;
use App\Helpers\HtmlTemplatesHelper;
use App\MembershipLevel;

class MembershipLevelController extends BaseController
{

    public function index()
    {
        if (request()->ajax()) {
            return datatables(MembershipLevel::filters(request()))->addColumn('discount',static function($data){
                    if($data->discount_type === MembershipLevel::DISCOUNT_TYPE_FIXED){
                        $final =  'Fixed RM '.$data->discount_value;
                    }else{
                        $final = $data->discount_value.'%';
                    }
                    return $final;
            })->addColumn('actions', static function($data){
                return HtmlTemplatesHelper::get_action_dropdown($data, '', false,false, auth()->user()->can('MembershipLevels.Update'),auth()->user()->can('MembershipLevels.Delete'),false,false);
            })->addColumn('status', function($data){
                return Helper::get_status($data->status);
            })->rawColumns(['actions', 'status'])->make(true);
        }
        return view('pages.membership-levels.list', ['add_url' => route("membership-levels.create"), 'breadcrumbs' => Breadcrumbs::generate('membership-levels')]);
    }

    /**
     * @inheritDoc
     */
    public function create()
    {
        return view('pages.membership-levels.add', ['title' => 'Add Membership Level']);
    }

    public function store(Request $request)
    {
        $rules = MembershipLevel::$validation;
        $validation_rules = $this->validate_lang_tabs(false, false);
        $rules = array_merge($rules, $validation_rules['rules']);

        request()->validate($rules, $validation_rules['customMessages']);

        $image = '';
        if ($request->has('image')) {
            $image = $request->file('image');
        }
        $request->request->add(['image' => $image]);
        $membershipLevel = new MembershipLevel();
        $membershipLevel->fill($request->all());
        $membershipLevel->save();

        return Helper::jsonMessage($membershipLevel->id !== null, MembershipLevel::INDEX_URL);
    }

     /**
     * @inheritDoc
     */
    public function edit($id)
    {
        $item = MembershipLevel::find($id);
        return view('pages.membership-levels.add', ['title' => 'Edit Membership Level', 'item'=> $item]);
    }

    public function update($id,Request $request)
    {
        $rules = MembershipLevel::$validation;
        $validation_rules = $this->validate_lang_tabs(false, false);
        $rules = array_merge($rules, $validation_rules['rules']);

        request()->validate($rules, $validation_rules['customMessages']);

        $image = '';
        if ($request->has('image')) {
            $image = $request->file('image');
        }
        $request->request->add(['image' => $image]);

        $membershipLevel =  MembershipLevel::findOrFail($id);
        $membershipLevel->fill($request->all());
        $membershipLevel->save();

        return Helper::jsonMessage($membershipLevel->id !== null, MembershipLevel::INDEX_URL);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id)
    {

        $ml = MembershipLevel::query()->findOrFail($id);
        if($ml->club->count()> 0){
            return Helper::jsonMessage(false, NULL,  "Can't delete, membership is assigned to ".$ml->club->name);
        }
        try {
            $ml->delete();
            return Helper::jsonMessage(true, NULL, 'Record Successfully deleted');
        } catch (\Exception $e) {
            return Helper::jsonMessage(false, NULL,  $e->getMessage());
        }
    }
}
