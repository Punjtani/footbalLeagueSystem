<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

//    public function sendResetLinkEmail(Request $request)
//    {
//        $this->validate($request, ['email' => 'required|email']);
//        $user_check = User::query()->where('email', $request->email)->first();
//
//        if ($user_check === NULL) {
//            return back()->with('status', "User Doesn't Exists");
//        }
//
//        if ($user_check->status !== User::STATUS_PUBLISH) {
//            return back()->with('status', 'Your account is not activated. Please activate it first.');
//        }
//
//        $response = $this->broker()->sendResetLink(
//            $request->only('email')
//        );
//
//        if ($response === Password::RESET_LINK_SENT) {
//            return back()->with('status', trans($response));
//        }
//
//        return back()->withErrors(
//            ['email' => trans($response)]
//        );
//    }
}
