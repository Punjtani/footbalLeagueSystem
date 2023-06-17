<?php

namespace App\Http\Controllers;

use App\BookingRule;
use App\Club;
use App\Helpers\Helper;
use App\Helpers\HtmlTemplatesHelper;
use App\Models\PropertyPhoto;
use App\Sport;
use App\Stadium;
use App\StadiumFacility;
use App\StadiumGallery;
use DaveJamesMiller\Breadcrumbs\Facades\Breadcrumbs;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\View\View;
use Monarobase\CountryList\CountryListFacade as Countries;

class StadiumController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return Factory|View
     */
    public function index()
    {
        try {
            if (request()->ajax()) {
                return datatables(Stadium::filters(request())->select('stadiums.*'))->addColumn('actions', static function ($data) {
                    return HtmlTemplatesHelper::get_action_dropdown($data, '', false, false, auth()->user()->can('Locations.Update'), auth()->user()->can('Locations.Delete'), false,false);
                })->addColumn('image', static function ($data) {
                    return Helper::get_name_with_image('', $data->image);
                })->rawColumns(['image', 'actions', 'status'])->make(true);
            }
        } catch (Exception $ex) {

        }
        return view('pages.stadiums.list', ['add_url' => route('stadiums.create'), 'button_text' => 'Add Location', 'advance_filters' => $this->advance_filters(), 'breadcrumbs' => Breadcrumbs::generate('stadiums')]);
    }

    private function advance_filters(): array
    {
        $club_list = Club::query()->select('id', 'name')->where('status', Club::STATUS_PUBLISH)->get()->toArray();
        $clubs = array();
        foreach ($club_list as $club) {
            $clubs[$club['id']] = $club['name'];
        }
        return array(
            'Club' => array('type' => 'dropdown', 'sub_type' => '', 'placeholder' => 'Select Club', 'name' => 'club_id', 'data' => $clubs),
            'Country' => array('type' => 'dropdown', 'sub_type' => '', 'placeholder' => 'Select Country', 'name' => 'country', 'data' => Countries::getList('en', 'php')),
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Factory|View
     */
    public function create()
    {
        $countries = Countries::getList('en', 'php');
        $sportsOptions = Sport::pluck('name', 'id');
        $bookingRuleOptions = BookingRule::pluck('name', 'id');

        return view('pages.stadiums.add', [
            'title' => 'Add Location',
            'countries' => $countries,
            'breadcrumbs' => Breadcrumbs::generate('stadiums.create'),
            'sportsOptions' => $sportsOptions,
            'bookingRuleOptions' => $bookingRuleOptions
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $rules = Stadium::$validation;
        $validation_rules = $this->validate_lang_tabs(false);
        $rules = array_merge($rules, $validation_rules['rules']);

        request()->validate($rules, $validation_rules['customMessages']);
        $image = '';
        if ($request->has('image')) {
            $image = $request->file('image');
        }
        if ($request->has('mobile_image')) {
            $mobile_image = $request->file('mobile_image');
        }
    
        
        $request->request->add(['image' => $image]);
        $request->request->add(['mobile_image' => $mobile_image]);
        $request->request->add(['is_display_frontend' => $request->is_display_frontend === '1' ? true : false]);
        $stadium = new Stadium;
        $stadium->fill($request->all());
        $stadium->save();
       
        $stadium->facilities()->createMany(request()->locationFacilities);
        return Helper::jsonMessage($stadium->id !== null, Stadium::INDEX_URL);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return Factory|View
     */
    public function edit($id)
    {
        $stadium = Stadium::query()->findOrFail($id);
        $countries = Countries::getList('en', 'php');
        $bookingRuleOptions = BookingRule::pluck('name', 'id');
        return view('pages.stadiums.add', [
            'title' => 'Edit Location',
            'countries' => $countries,
            'item' => $stadium,
            'breadcrumbs' => Breadcrumbs::generate('stadiums.edit', $stadium),
            'sportsOptions' => Sport::pluck('name', 'id'),
            'bookingRuleOptions' => $bookingRuleOptions
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $rules = Stadium::$validation;
        $validation_rules = $this->validate_lang_tabs(false);
        $rules = array_merge($rules, $validation_rules['rules']);
        unset($rules['image']);
        unset($rules['mobile_image']);
        request()->validate($rules, $validation_rules['customMessages']);
        $stadium = Stadium::query()->findOrFail($id);
        if (request()->has('image')) {
            request()->request->add(['image' => request()->file('image')]);
        }
        if (request()->has('mobile_image')) {
            request()->request->add(['mobile_image' => request()->file('mobile_image')]);
        }
        // dd( request()->file('mobile_image')->getClientOriginalName());
        $request->request->add(['is_display_frontend' => $request->is_display_frontend === '1' ? true : false]);
        $stadium->fill(request()->all());
        $stadium->save();
        //
        $newFacilities = [];
        foreach (request()->locationFacilities as $lf) {
            if (!empty($lf["id"])) {
                StadiumFacility::where('id', $lf["id"])->update($lf);
            } else {
                $newFacilities[] = new StadiumFacility($lf);
            }
        }
        $stadium->facilities()->saveMany($newFacilities);


        return Helper::jsonMessage($stadium !== null, Stadium::INDEX_URL, $stadium !== null ? 'Record Successfully Updated' : 'Unable to Update');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {

        $stadium = Stadium::query()->findOrFail($id);
        if ($stadium->bookings()->count() > 0) {
            return Helper::jsonMessage(false, NULL, "Can't delete, Booking exists for this location");
        }
        try {
            $stadium->delete();
            return Helper::jsonMessage(true, NULL, 'Record Successfully deleted');
        } catch (Exception $e) {
            return Helper::jsonMessage(false, NULL, $e->getMessage());
        }
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function gallery($id)
    {
        $stadium = Stadium::query()->findOrFail($id);
        return view('pages.stadiums.gallery', [
            'title' => 'Gallery',
            'stadium' => $stadium,
            'breadcrumbs' => Breadcrumbs::generate('stadiums.gallery', $stadium),
        ]);

    }

    function upload(Request $request)
    {
        $imageFile = $request->file('file');
        $imageName = time() . '.' . $imageFile->extension();
        $image = '';
        if ($request->has('file')) {
            $image = $request->file('file');
        }
        $request->request->add(['image' => $image]);
        $stadiumGallery = new StadiumGallery;
        $stadiumGallery->fill($request->all());
        $stadiumGallery->save();

        return response()->json(['success' => $imageName]);
    }

    function fetch($id)
    {
        $images = StadiumGallery::whereStadiumId($id)->get();

        $output = '<div class="row">';
        foreach ($images as $data) {
            $output .= '
      <div class="col-md-2" style="margin-bottom:16px;" align="center">
                <img src="' . $data->image . '" class="img-thumbnail" width="175" height="175" style="height:175px;" />
                <button type="button" class="btn btn-link remove_image" id="' . $data->id . '" data-name="' . $data->image . '">Remove</button>
            </div>
      ';
        }
        $output .= '</div>';
        echo $output;
    }

    function deleteMedia(Request $request)
    {
        StadiumGallery::where('id', $request->id)->delete();

    }
}
