<?php

namespace App\Http\Controllers;

use App\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index()
    {
        $setting = Settings::where('id', 1)->first();
        return view('pages.settings.index', compact('setting'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //return $request->all();
        DB::table('settings')
            ->where('id', 1)
            ->update(['title' => $request->title]);
        Session::flash('message', 'Title Updated Successfully.');
        return redirect('back-end/settings');
    }

    public function title(Request $request)
    {
        //return $request->all();
        DB::table('settings')
            ->where('id', 1)
            ->update(['title' => $request->title]);

        Session::flash('message', 'Website Content Updated Successfully.');
        return redirect('back-end/settings');
    }

    public function about(Request $request)
    {
        //return $request->all();
        DB::table('settings')
            ->where('id', 1)
            ->update(['about' => $request->about]);


        Session::flash('message', 'About Us Text Updated Successfully.');
        return redirect('back-end/settings');
    }

    public function address(Request $request)
    {
        //return $request->all();
        DB::table('settings')
            ->where('id', 1)
            ->update(['address' => $request->address,
                'phone' => $request->phone,
                'fax' => $request->fax,
                'email' => $request->email]);
        Session::flash('message', 'Address Updated Successfully.');
        return redirect('back-end/settings');
    }

    public function socialLinks(Request $request)
    {
        DB::table('settings')
            ->where('id', 1)
            ->update(['social_links' => json_encode($request->except('_token'))]);
        Session::flash('message', 'Social Links Updated Successfully.');
        return redirect('back-end/settings');
    }

    public function footer(Request $request)
    {

        DB::table('settings')
            ->where('id', 1)
            ->update(['footer' => $request->footer]);
        Session::flash('message', 'Footer Updated Successfully.');
        return redirect('back-end/settings');
    }

    public function verification(Request $request)
    {

        DB::table('settings')
            ->where('id', 1)
            ->update(['is_otp_enable' => $request->is_otp_enable ?? 0, 'forgot_password_attempt' => $request->forgot_password_attempt]);
        Session::flash('message', 'Verification data Updated Successfully.');
        return redirect('back-end/settings');
    }

    public function logo(Request $request)
    {
        $logo = $request->file('logo');
        $name = $logo->getClientOriginalName();
        $logo->move(public_path('/front-end/images/'), $name);
        DB::table('settings')
            ->where('id', 1)
            ->update(['logo' => '/front-end/images/' . $name]);
        Session::flash('message', 'Website Logo Updated Successfully.');
        return redirect('back-end/settings');
    }

    public function favicon(Request $request)
    {
        $logo = $request->file('favicon');
        $name = $logo->getClientOriginalName();
        $logo->move(public_path('/front-end/images/'), $name);
        DB::table('settings')
            ->where('id', 1)
            ->update(['favicon' => '/front-end/images/' . $name]);
        Session::flash('message', 'Website Favicon Updated Successfully.');
        return redirect('back-end/settings');
    }

    public function background(Request $request)
    {
        ini_set('upload_max_filesize', '100G');
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);
        $logo = $request->file('background');
        $name = $logo->getClientOriginalName();
        $logo->move(public_path('/front-end/images/'), $name);
        DB::table('settings')
            ->where('id', 1)
            ->update(['background' => '/front-end/images/' . $name]);
        Session::flash('message', 'Background Image Updated Successfully.');
        return redirect('back-end/settings');
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
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        //return $request->all();

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
