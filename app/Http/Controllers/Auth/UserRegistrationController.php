<?php

namespace App\Http\Controllers\Auth;

use App\Settings;
use App\Traits\GeneralHelperTrait;
use App\User;
use App\Http\Controllers\Controller;
use App\UserOtp;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Twilio\Rest\Client;
use App\Rules\IsValidPassword;
class UserRegistrationController extends Controller
{
    use GeneralHelperTrait;


    function signIn(Request $request)
    {
        $phoneNumber = $this->setPhoneAttribute($request->phone_number);
        $user = User::where('phone', $phoneNumber)->where('user_type', 'user')->first();
        if (is_null($user)) {
            return $this->errorResponse(400, 'This phone number is not registered with us!', $phoneNumber);
        }
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        } else {
            $remember_me = $request->has('remember_me') ? true : false;
            $credentials = ['phone' => $phoneNumber, 'user_type' => 'user', 'password' => $request->password];
            if (auth()->attempt($credentials, $remember_me)) {
                $setting = Settings::whereId('1')->first(['is_otp_enable', 'forgot_password_attempt']);
                if ($setting->is_otp_enable == 1) {
                    $userOtp = UserOtp::whereUserId($user->id)->whereType('registration')->whereStatus('pending')->first();
                    if (!is_null($userOtp)) {
//                    $this->sendWhatsappNotification($userOtp->otp, $user->phone);
                        return $this->successResponse(200, 'code-sent', $user->phone, true);
                    } else {
                        return $this->successResponse(200, 'verified', $user->phone, true);
                    }
                } else {
                    Auth::login($user);
                    return $this->successResponse(200, 'User sign in successfully', $user->phone, false);
                }
            } else {
                return $this->errorResponse(402, 'Your password not matched in our records', $user->phone);
            }

        }
    }

    function signUp(Request $request)
    {
        $phoneNumber = $this->setPhoneAttribute($request->phone_number);
        $user = User::where('phone', $phoneNumber)->where('user_type', 'user')->first();
        if (!is_null($user)) {
            return $this->errorResponse(400, 'This phone number already exist', $phoneNumber);
        }
        $validator = Validator::make($request->all(), [
            'full_name' => 'required',
            'phone_number' => 'required',
            'password' => [
                'required',
                'string',
                new isValidPassword(),
            ],

        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        } else {
            $otp = rand(1000, 9999);
            $user = new User();
            $user->name = $request->full_name;
            $user->password = Hash::make($request->password);
            $user->phone = $this->setPhoneAttribute($request->phone_number);
            $user->login_token = Str::random(32);
            $user->user_type = 'user';
            $user->subscription_id = $this->subscriptionNumber();
            $user->save();
            $role = Role::whereName('User')->first();
            $user->assignRole($role);

            $setting = Settings::whereId('1')->first(['is_otp_enable', 'forgot_password_attempt']);
            if ($setting->is_otp_enable == 1) {
                UserOtp::create([
                    'user_id' => $user->id,
                    'otp' => $otp,
                    'status' => 'pending',
                    'type' => 'registration'
                ]);
                // $this->sendWhatsappNotification($otp, $user->phone);
                return $this->successResponse(200, 'We sent a verification code in your whatsapp number to verify your phone number', $user->phone, true);
            } else {
                $user->status = 1;
                $user->save();
                Auth::login($user);
                return $this->successResponse(200, 'User Registration successfully', $user->phone, false);

            }


        }
    }

    function forgotPassword(Request $request)
    {
        $phoneNumber = $this->setPhoneAttribute($request->phone_number);
        $user = User::where('phone', $phoneNumber)->where('user_type', 'user')->first();
        if (is_null($user)) {
            return $this->errorResponse(400, 'This phone number is not registered with us!', $phoneNumber);
        }
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        } else {
            $setting = Settings::whereId('1')->first(['is_otp_enable', 'forgot_password_attempt']);

            $forgotAttemptCount = UserOtp::whereUserId($user->id)->whereType('forgot-password')->whereDate('created_at', date('Y-m-d'))->count();
            if ($setting->forgot_password_attempt == $forgotAttemptCount) {
                return $this->errorResponse(401, 'You already done 2 attempts for today.Please try again tomorrow.', $user->phone);
            } else {
                if ($setting->is_otp_enable == 1) {
                    $otp = rand(1000, 9999);
                    UserOtp::create([
                        'user_id' => $user->id,
                        'otp' => $otp,
                        'status' => 'pending',
                        'type' => 'forgot-password'
                    ]);
//                $this->sendWhatsappNotification($otp, $user->phone);
                    return $this->successResponse(200, 'code-sent', $user->phone, true);
                } else {
                    return $this->successResponse(200, 'Phone number found', $user->phone, false);
                }
            }


        }

    }

    function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phoneNumber' => 'required',
            'password' => [
                'required',
                'string',
                new isValidPassword(),
            ],

        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);

        } else {
            $user = User::where('phone', $request->phoneNumber)->where('user_type', 'user')->first();
            $user->password = Hash::make($request->password);
            $user->save();
            return $this->successResponse(200, 'Password updated successfully.', $user->phone, null);

        }
    }

    function verifyOTPCode(request $request)
    {
        $phone = $request->phone;
        $otp = $request->otp;

        $user = User::where('phone', $phone)->where('user_type', 'user')->first();
        if (!$user) {
            return $this->errorResponse(400, 'This phone number is not registered with us!', $phone);
        }

        if ($request->type == 'forgot') {
            $count = UserOtp::whereUserId($user->id)->whereType('forgot-password')->whereOtp($otp)->whereStatus('pending')->count();
            if ($count > 0) {
                $status = 200;
                $message = "This phone number successfully verified.";
                UserOtp::where('user_id', $user->id)
                    ->where('otp', $otp)
                    ->where('type', 'forgot-password')
                    ->update(['status' => 'verified', 'otp' => 0000]);
            } else {
                $status = 400;
                $message = "Incorrect verification code";

            }
        } else {
            $count = UserOtp::whereUserId($user->id)->whereType('registration')->whereOtp($otp)->whereStatus('pending')->count();
            if ($count > 0) {
                $status = 200;
                $message = "This phone number successfully verified.";

                $user->email_verified_at = Carbon::now();
                User::where('phone', $phone)->where('user_type', 'user')
                    ->update(['email_verified_at' => Carbon::now(), 'status' => 1]);
                UserOtp::where('user_id', $user->id)
                    ->where('otp', $otp)
                    ->where('type', 'registration')
                    ->update(['status' => 'verified', 'otp' => 0000]);

                Auth::login($user);

            } else {
                $status = 400;
                $message = "Incorrect verification code";
            }
        }
        return $this->successResponse($status, $message, $user->phone, null);
    }

    private function sendWhatsappNotification(string $otp, string $recipient)
    {
        $twilio_whatsapp_number = config('app.twilio')['TWILIO_WHATSAPP_NUMBER']; //Or SAND_BOX number
        $account_sid = config('app.twilio')['TWILIO_ACCOUNT_SID'];
        $auth_token = config('app.twilio')['TWILIO_AUTH_TOKEN'];

        $client = new Client($account_sid, $auth_token);
        $message = "Your Metahub verification code is:  $otp";
        return $client->messages->create("whatsapp:$recipient", array(
            'from' => "whatsapp:$twilio_whatsapp_number", 'body' => $message));
    }

    public function setPhoneAttribute($phone_number)
    {
        if (empty($phone_number)) {
            return null;
        }
        $phone = str_replace("-", "", $phone_number);
        if ($phone[0] !== '+') {
            $phone = "+60" . ltrim($phone, "0");
        }

        return $phone;
    }

    public function successResponse($status, $message, $phone, $enable)
    {
        return response()->json(
            [
                "status" => $status,
                'enable' => $enable,
                "msg" => $message,
                'phone' => $phone
            ]);
    }

    public function errorResponse($status, $message, $phone)
    {
        return response()->json(
            [
                "status" => $status,
                "msg" => $message,
                'phone' => $phone
            ]);
    }


}
