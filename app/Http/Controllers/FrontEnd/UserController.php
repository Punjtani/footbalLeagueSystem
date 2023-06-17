<?php

namespace App\Http\Controllers\FrontEnd;

use App\Booking;
use App\Club;
use App\Http\Controllers\Controller;
use App\Mail\DeleteUserAccount;
use App\Mail\UserInvitation;
use App\Match;
use App\Squad;
use App\Subscription;
use App\SubscriptionHistory;
use App\Tournament;
use App\Traits\BelongsToTenant;
use App\Traits\GeneralHelperTrait;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    use BelongsToTenant;
    use GeneralHelperTrait;

    public function dashboard()
    {

        $toDate = Carbon::parse(date('Y-m-d'));
        $fromDate = Carbon::parse(date('Y-m-01'));
        $daysOfSubscription = $toDate->diffInDays($fromDate);
        $user = Auth::user();

        $subscription = Subscription::whereUserId($user->id)->first();
        $team = Club::whereSubscriptionId(Auth::user()->subscription_id)->orderBy('id', 'asc')->first();
        return view('front-end.user.dashboard', compact('user', 'subscription','daysOfSubscription','team'));
    }

    public function updateProfile(Request $request)
    {

        $request->phone = $this->setPhoneAttribute($request->phone);
        $user = User::find(auth()->user()->id);
        $user->is_save = false;
        $user->save();
        $this->validate($request, [
            'full_name' => 'required',
            'email' => ['required', Rule::unique('admins')->ignore($user->id)],
            // 'phone' => ['required', Rule::unique('admins')->ignore($user->id)],
            'phone' => 'required',
            'post_code' => 'required',
            'gender' => 'required',
            'date_of_birth' => 'required',
        ], [
        ]);

        $user->update([
            'name' => $request->full_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'post_code' => $request->post_code,
            'gender' => $request->gender,
            'user_type' => 'user',
            'date_of_birth' => $request->date_of_birth,
            'is_save' => true
        ]);


        return redirect()->route('user.dashboard')
            ->with([
                'flash_status' => 'success',
                'flash_message' => 'Profile updated successfully.'
            ]);
    }

    public function account()
    {
        $user = Auth::user();
        return view('front-end.user.account-edit', compact('user'));
    }

    public function accountUpdate(Request $request)
    {

        $user = User::find(auth()->user()->id);
        if ($request->type == 'email') {
            $this->validate($request, [
                'email' => ['required', Rule::unique('admins')->ignore($user->id)],
            ]);
            $user->update([
                'email' => $request->email,
            ]);
        }
        if ($request->type == 'password') {
            $this->validate($request, [
                'password' => 'required|min:8',
            ]);
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }
        if ($request->type == 'delete_account') {
            $user_data = auth()->user();
            SubscriptionHistory::where('user_id', $user_data->id)->delete();
            Subscription::where('user_id', $user_data->id)->delete();
            Club::where('subscription_id', $user_data->subscription_id)->update(['subscription_id' => null]);
            if (isset($user->email) && !is_null($user->email)) {
                Mail::to($user->email)->send(new DeleteUserAccount($user));
            }
            User::find($user_data->id)->delete();
            auth()->logout();
            return redirect()->route('landing')
                ->with([
                    'flash_status' => 'success',
                    'flash_message' => 'Account deactivated successfully.'
                ]);
        }


        return redirect()->route('user.account.edit')
            ->with([
                'flash_status' => 'success',
                'flash_message' => 'Account Information updated successfully.'
            ]);
    }

    public function subscriptions(Request $request)
    {
        $billingHistory = SubscriptionHistory::whereUserId(Auth::id())->get();
        $subscription = Subscription::whereUserId(Auth::id())->first();
        $user = Auth::user();
        return view('front-end.user.subscription', compact('billingHistory', 'subscription', 'user'));
    }

    public function learnMoreMembership()
    {
        $url = config('app.curlec')['url'];
        $data = 'method=10&merchantId=' . config('app.curlec')['merchant_id'];
        $response = $this->curlRequest($url, $data);

        if ($response['data']['Status'][0] == 200) {
            $package = $response['data']['Response'][0]['list'][0];
            $user = Auth::user();
            return view('front-end.user.learn-more-membership', compact('package', 'user'));
        } else {
            return redirect()->back()
                ->with([
                    'flash_status' => 'success',
                    'flash_message' => 'Subscription not found.'
                ]);
        }

    }

    public function bookings(Request $request)
    {
        $team = Club::whereSubscriptionId(Auth::user()->subscription_id)->orderBy('id', 'asc')->first();

        if (!is_null($team)) {
            $bookings = Booking::where('club1_id', $team->id)->orWhere('club2_id', $team->id)->has('match');
            if (isset($request->filter)) {
                if ($request->filter == '7') {
                    $date = Carbon::today()->subDays(7);
                } elseif ($request->filter == '30') {
                    $date = Carbon::today()->subDays(30);
                } elseif ($request->filter == '90') {
                    $date = Carbon::today()->subDays(90);
                }
                $bookings->where('created_at', '>=', $date);
            }
            $bookings = $bookings->with([
                'stadium' => function ($query) {
                    $query->select('id', 'name', 'image');
                },
                'match',
                'tournament' => function ($query) {
                    $query->select('id', 'name', 'image');
                }
            ])->take(3)->get(['id', 'stadium_id', 'stadium_facility_id', 'booking_date', 'start_time', 'end_time', 'tournament_id', 'club1_id', 'club2_id', 'fee_type', 'club1_fee', 'slot_fee']);
        } else {
            $bookings = [];
        }


        return view('front-end.user.bookings', compact('bookings'));
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

    public function loadMoreBookings(Request $request)
    {
        $output = '';
        $id = $request->id;


        $team = Club::whereSubscriptionId(Auth::user()->subscription_id)->orderBy('id', 'asc')->first();
        $bookings = Booking::where('id', '>', $id)->where('club1_id', $team->id)->orWhere('club2_id', $team->id)->has('match');
        if (isset($request->filter)) {
            if ($request->filter == '7') {
                $date = Carbon::today()->subDays(7);
                $bookings->where('created_at', '>=', $date);
            } elseif ($request->filter == '30') {
                $date = Carbon::today()->subDays(30);
                $bookings->where('created_at', '>=', $date);
            } elseif ($request->filter == '90') {
                $date = Carbon::today()->subDays(90);
                $bookings->where('created_at', '>=', $date);
            }

        }
        $bookings = $bookings->with([
            'stadium' => function ($query) {
                $query->select('id', 'name', 'image');
            },
            'match',
            'tournament' => function ($query) {
                $query->select('id', 'name', 'image');
            }
        ])->orderBy('id', 'asc')->take(3)->get(['id', 'stadium_id', 'stadium_facility_id', 'booking_date', 'start_time', 'end_time', 'tournament_id', 'club1_id', 'club2_id', 'fee_type', 'club1_fee', 'slot_fee']);

        $response = array('status' => '', 'message' => "", 'data' => array());


        if (!$bookings->isEmpty() && ($bookings->count() > 0)) {
            $bookingId = null;
            $output = '';
            foreach ($bookings as $booking) {
                $url = $booking->stadium->image;
                $bookingId = $booking->id;
                if ($booking->fee_type == 'per_team') {
                    $price = $booking->club1_fee;
                } else {
                    $price = $booking->slot_fee;
                }
                $output .= ' <div class="booked-price">
                            <div class="img"><img class="one" src="' . $url . '"
                                                  alt="sub"></div>
                            <div class="text">
                                <div class="location">
                                    <div class="details">
                                        <p>Location & Details <span>:</span></p>
                                    </div>
                                    <div class="footbal">
                                        <p>' . $booking->stadium->name . ' <span>' . date('d F Y', strtotime($booking->booking_date)) . ',' . date('D', strtotime($booking->booking_date)) . '</span>
                                            <span>' . date('h:i A', strtotime($booking->start_time)) . ' - ' . date('h:i A', strtotime($booking->end_time)) . '</span></p>

                                    </div>
                                </div>
                                <div class="price-boked">
                                    <p>Booked price</p>
                                    <strong><span>RM </span>' . $price . '</strong>
                                </div>

                            </div>
                        </div>';
            }

            $data = [
                'booking_id' => $bookingId,
                'output' => $output
            ];
            $response['status'] = 'success';
            $response['data'] = $data;

        }

        return $response;
    }

    public function teams(Request $request)
    {
        $user = Auth::user();
        $squads = Squad::whereUserId($user->id)->get();
        $team = Club::whereSubscriptionId($user->subscription_id)->orderBy('id', 'asc')->first();
        $tournament = Tournament::where('booking_type','league_booking')->where('status', 1)->where(function ($query) {
            $query->orWhereNull('hide_frontend');
            $query->orWhere('hide_frontend', false);
        })->latest()->first();

        $tournaments = Tournament::where('booking_type','league_booking')->where('status', 1)->where(function ($query) {
            $query->orWhereNull('hide_frontend');
            $query->orWhere('hide_frontend', false);
        })->orderBy('id', 'desc')->take(3)->get();
        $subscription = Subscription::whereUserId($user->id)->first();


        if (!is_null($team)) {

            foreach ($tournaments as $tourna) {
                $teamNames = $this->uniqueTeams($tourna->id, $team->id, $tourna->name);
                $leagues[] = $this->leagueTable($tournament->bookings, $teamNames);
            }
            /////////////////////////// League Booking /////////////////////////////
            $bookings = Booking::whereHas('tournament',function($q){
                 $q->where('booking_type','league_booking');
            })->has('match')->whereDate('booking_date', "<=", Carbon::now()->format('Y-m-d'))
              ->where('club1_id', $team->id)->orWhere('club2_id', $team->id);
            $bookings = $bookings->with([
                'stadium' => function ($query) {
                    $query->select('id', 'name', 'image');
                },
                'match',
                'tournament' => function ($query) {
                    $query->where('booking_type','league_booking')->select('id', 'name', 'image','booking_type');
                }
            ])->orderBy('booking_date', 'desc')
                ->orderBy('start_time')
                ->take(6)
                ->get(['id', 'stadium_id', 'stadium_facility_id', 'booking_date', 'start_time', 'end_time', 'tournament_id', 'club1_id', 'club2_id', 'fee_type', 'club1_fee', 'slot_fee']);
          ////////////////////////////////End League Booking ////////////////////////////////////

          /////////////////////////// User Booking /////////////////////////////
              $user_bookings = Booking::whereHas('tournament',function($q){
                $q->where('booking_type','user_booking');
                })->has('match')->whereDate('booking_date', "<=", Carbon::now()->format('Y-m-d'))
                ->where('club1_id', $team->id)
                ->orWhere('club2_id', $team->id);
             $user_bookings = $user_bookings->with([
               'stadium' => function ($query) {
                   $query->select('id', 'name', 'image');
               },
               'match',
               'tournament' => function ($query) {
                   $query->where('booking_type','user_booking')->select('id', 'name', 'image','booking_type');
               }
             ])->orderBy('booking_date', 'desc')
               ->orderBy('start_time')
               ->take(6)
               ->get(['id', 'stadium_id', 'stadium_facility_id', 'booking_date', 'start_time', 'end_time', 'tournament_id', 'club1_id', 'club2_id', 'fee_type', 'club1_fee', 'slot_fee']);

              /////////////////////////// End User Booking /////////////////////////////
            } else {
            $leagues = [];
            $bookings = [];
            $user_bookings = [];
        }

        return view('front-end.user.teams', compact('user_bookings','bookings', 'tournament', 'user', 'team', 'squads', 'leagues', 'subscription'));
    }


    public function addSquad(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'squad_name' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        } else {
            $data = Squad::create([
                'user_id' => $request->user_id,
                'name' => $request->squad_name,
            ]);
            return response()->json(
                [
                    "status" => 200,
                    "message" => 'Squad Member added Successfully.',
                    "data" => $data

                ]);
        }


    }

    public function removeSquad(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'remove_squad' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        } else {
            Squad::whereId($request->remove_squad)->delete();
            return response()->json(
                [
                    "status" => 200,
                    "message" => 'Squad Member deleted Successfully.',
                ]);
        }


    }


    public function uniqueTeams($id, $team_id, $tournamentName)
    {
        $matches = Match::with(['booking', 'booking.club1', 'booking.club2'])
            ->whereHas('booking', function ($query) use ($id, $team_id) {
                $query->where('tournament_id', $id)->where('club1_id', $team_id)->orWhere('club2_id', $team_id);
            })->get();
        $teamNames = [];
        $i = 0;
        foreach ($matches as $match) {
            $teamNames[$i]["id"] = $match->booking && $match->booking->club1 ? $match->booking->club1->id : '-';
            $teamNames[$i]["name"] = $match->booking && $match->booking->club1 ? $match->booking->club1->name : '-';
            $teamNames[$i]["tournament_name"] = $tournamentName ?? '-';
            if ($match->booking && $match->booking->club1 && !empty($match->booking->club1->getRawOriginal('image'))) {
                $teamNames[$i]["image"] = $match->booking->club1->image;
            } else {
                $teamNames[$i]["image"] = '' . asset('images/empty_logo.svg') . '';
            }
            $teamNames[$i]["games"] = 0;
            $teamNames[$i]["wins"] = 0;
            $teamNames[$i]["draw"] = 0;
            $teamNames[$i]["lose"] = 0;
            $teamNames[$i]["F"] = 0;
            $teamNames[$i]["A"] = 0;
            $teamNames[$i]["GD"] = 0;
            $teamNames[$i]["points"] = 0;
            $i++;

            $teamNames[$i]["id"] = $match->booking && $match->booking->club2 ? $match->booking->club2->id : '-';
            $teamNames[$i]["tournament_name"] = $tournamentName ?? '-';
            $teamNames[$i]["name"] = $match->booking && $match->booking->club2 ? $match->booking->club2->name : '-';
            if ($match->booking && $match->booking->club2 && !empty($match->booking->club2->getRawOriginal('image'))) {
                $teamNames[$i]["image"] = $match->booking->club2->image;
            } else {
                $teamNames[$i]["image"] = '' . asset('images/empty_logo.svg') . '';
            }
            $teamNames[$i]["games"] = 0;
            $teamNames[$i]["wins"] = 0;
            $teamNames[$i]["draw"] = 0;
            $teamNames[$i]["lose"] = 0;
            $teamNames[$i]["F"] = 0;
            $teamNames[$i]["A"] = 0;
            $teamNames[$i]["GD"] = 0;
            $teamNames[$i]["points"] = 0;
            $i++;
        }
        $teamNames = array_values(array_unique($teamNames, SORT_REGULAR));
        return $teamNames;
    }

    public function leagueTable($bookings, $teamNames)
    {
        foreach ($bookings as $booking) {
            if (!is_null($booking->match)) {
                if ($booking->match->match_status == 'completed') {
                    foreach ($teamNames as $key => $team) {
                        if ($booking->match->match_result == 'team_1_win') {
                            if ($team['id'] == $booking->club1->id) {
                                $teamNames[$key]['games'] = $teamNames[$key]['games'] + 1;
                                $teamNames[$key]['wins'] = $teamNames[$key]['wins'] + 1;
                                $teamNames[$key]['F'] = $teamNames[$key]['F'] + $booking->match->team_1_score ?? 0;
                                $teamNames[$key]['A'] = $teamNames[$key]['A'] + $booking->match->team_2_score ?? 0;
                                $teamNames[$key]['GD'] = $teamNames[$key]['F'] - $teamNames[$key]['A'];
                                $teamNames[$key]['points'] = ($teamNames[$key]['wins'] * 3) + ($teamNames[$key]['draw'] * 1) + ($teamNames[$key]['lose'] * 0);
                            }
                            if ($team['id'] == $booking->club2->id) {
                                $teamNames[$key]['games'] = $teamNames[$key]['games'] + 1;
                                $teamNames[$key]['lose'] = $teamNames[$key]['lose'] + 1;
                                $teamNames[$key]['F'] = $teamNames[$key]['F'] + $booking->match->team_2_score ?? 0;
                                $teamNames[$key]['A'] = $teamNames[$key]['A'] + $booking->match->team_1_score ?? 0;
                                $teamNames[$key]['GD'] = $teamNames[$key]['F'] - $teamNames[$key]['A'];
                                $teamNames[$key]['points'] = ($teamNames[$key]['wins'] * 3) + ($teamNames[$key]['draw'] * 1) + ($teamNames[$key]['lose'] * 0);
                            }

                        } elseif ($booking->match->match_result == 'team_2_win') {

                            if ($team['id'] == $booking->club2->id) {
                                $teamNames[$key]['games'] = $teamNames[$key]['games'] + 1;
                                $teamNames[$key]['wins'] = $teamNames[$key]['wins'] + 1;
                                $teamNames[$key]['F'] = $teamNames[$key]['F'] + $booking->match->team_2_score ?? 0;
                                $teamNames[$key]['A'] = $teamNames[$key]['A'] + $booking->match->team_1_score ?? 0;
                                $teamNames[$key]['GD'] = $teamNames[$key]['F'] - $teamNames[$key]['A'];
                                $teamNames[$key]['points'] = ($teamNames[$key]['wins'] * 3) + ($teamNames[$key]['draw'] * 1) + ($teamNames[$key]['lose'] * 0);

                            }
                            if ($team['id'] == $booking->club1->id) {
                                $teamNames[$key]['games'] = $teamNames[$key]['games'] + 1;
                                $teamNames[$key]['lose'] = $teamNames[$key]['lose'] + 1;
                                $teamNames[$key]['F'] = $teamNames[$key]['F'] + $booking->match->team_1_score ?? 0;
                                $teamNames[$key]['A'] = $teamNames[$key]['A'] + $booking->match->team_2_score ?? 0;
                                $teamNames[$key]['GD'] = $teamNames[$key]['F'] - $teamNames[$key]['A'];
                                $teamNames[$key]['points'] = ($teamNames[$key]['wins'] * 3) + ($teamNames[$key]['draw'] * 1) + ($teamNames[$key]['lose'] * 0);

                            }
                        } elseif ($booking->match->match_result == 'draw') {

                            if ($team['id'] == $booking->club1->id) {
                                $teamNames[$key]['games'] = $teamNames[$key]['games'] + 1;
                                $teamNames[$key]['draw'] = $teamNames[$key]['draw'] + 1;
                                $teamNames[$key]['F'] = $teamNames[$key]['F'] + $booking->match->team_1_score ?? 0;
                                $teamNames[$key]['A'] = $teamNames[$key]['A'] + $booking->match->team_2_score ?? 0;
                                $teamNames[$key]['GD'] = $teamNames[$key]['F'] - $teamNames[$key]['A'];
                                $teamNames[$key]['points'] = ($teamNames[$key]['wins'] * 3) + ($teamNames[$key]['draw'] * 1) + ($teamNames[$key]['lose'] * 0);
                            }
                            if ($team['id'] == $booking->club2->id) {
                                $teamNames[$key]['games'] = $teamNames[$key]['games'] + 1;
                                $teamNames[$key]['draw'] = $teamNames[$key]['draw'] + 1;
                                $teamNames[$key]['F'] = $teamNames[$key]['F'] + $booking->match->team_2_score ?? 0;
                                $teamNames[$key]['A'] = $teamNames[$key]['A'] + $booking->match->team_1_score ?? 0;
                                $teamNames[$key]['GD'] = $teamNames[$key]['F'] - $teamNames[$key]['A'];
                                $teamNames[$key]['points'] = ($teamNames[$key]['wins'] * 3) + ($teamNames[$key]['draw'] * 1) + ($teamNames[$key]['lose'] * 0);
                            }


                        }


                    }

                }
            }
        }
        return $teamNames;
    }

}


