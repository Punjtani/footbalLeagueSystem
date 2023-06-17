<?php

namespace App\Http\Controllers;

use App\Regions;
use App\Sport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AjaxController extends Controller
{
    public function stadiums(Request $request)
    {
        $response = array('status' => '', 'message' => "", 'data' => array());

        $validator = Validator::make($request->all(), [
            'sport_id' => 'required'
        ]);

        if (!$validator->fails()) {
            $sport = Sport::find($request->sport_id);
            $stadiums = [];
            foreach($sport->stadium_facilities as $key=>$facility){
                if($facility->status === 1){
                    $stadiums[$key]['id'] = $facility->stadium->id;
                    $stadiums[$key]['name'] = $facility->stadium->name;
                }
            }


            $response['status'] = 'success';
            $response['data'] = [
                'stadiums' => $stadiums,
            ];
        } else {
            $response['status'] = 'error';
            $response['message'] = "Validation Errors.";
            $response['data'] = $validator->errors()->toArray();
        }

        return $response;
    }

}
