<?php

namespace App\Http\Controllers\FrontEnd;


use App\Booking;
use App\Club;
use App\League;
use App\Mail\ContactUs;
use App\Mail\DeleteUserAccount;
use App\Match;
use App\Settings;
use App\Stadium;
use App\StadiumGallery;
use App\StaticPage;
use App\Team;
use App\Tournament;
use App\User;
use App\Sponsor;
use App\Sport;
use App\StadiumFacility;
use App\OverridePricing;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use DB;
use Intervention\Image\Facades\Image;
use Intervention\Image\Filters\FilterInterface as DemoFilter;
use Illuminate\Support\Facades\Input;
use \Illuminate\Http\Concerns\InteractsWithInput;
use Intervention\Image\ImageManager;
use Barryvdh\DomPDF\Facade\Pdf;
use Imagick;

class PagesController extends Controller
{

  use InteractsWithInput;
    public function index()
    {

        //Image migrated successfully
        $flag = false;

        $sponsors = Sponsor::where('status', 1)->get();
        $stadiums = Stadium::where('status', 1)->where('is_display_frontend', true)->get(['id', 'image', 'name', 'location', 'heading']);
        $background = Settings::where('id', 1)->first()->background;
        return view('front-end.pages.index', [
            'sponsors' => $sponsors,
            'stadiums' => $stadiums,
            'background' => $background
        ]);
    }


    public function uniqueTeams($id)
    {
        $matches = Match::with(['booking', 'booking.club1', 'booking.club2'])
            ->whereHas('booking', function ($query) use ($id) {
                $query->where('tournament_id', $id);
            })->get();
        $teamNames = [];
        $i = 0;
        if (!empty($matches) && count($matches) > 0) {
            foreach ($matches as $match) {
                $teamNames[$i]["id"] = $match->booking && $match->booking->club1 ? $match->booking->club1->id : '-';
                $teamNames[$i]["name"] = $match->booking && $match->booking->club1 ? $match->booking->club1->name : '-';
                if ($match->booking && $match->booking->club1 && !empty($match->booking->club1->getRawOriginal('image'))) {
                    $teamNames[$i]["image"] = $match->booking->club1->image;
                } else {
                    $teamNames[$i]["image"] = '' . asset('images/empty_logo.png') . '';
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
                $teamNames[$i]["name"] = $match->booking && $match->booking->club2 ? $match->booking->club2->name : '-';
                if ($match->booking && $match->booking->club2 && !empty($match->booking->club2->getRawOriginal('image'))) {
                    $teamNames[$i]["image"] = $match->booking->club2->image;
                } else {
                    $teamNames[$i]["image"] = '' . asset('images/empty_logo.png') . '';
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
        }


        return $teamNames;
    }
    public function image($time = null,$date = null,$location = null,$team_one_title =null,$team_two_title =null,$team_one_image =null,$team_two_image =null) 
    {
            // $location = base64_decode($location);
            if($team_one_title != null || $team_two_title !=null)
           {
                if($team_one_title == "empty" && $team_two_title !=null)
                {
                    $image_path = public_path('images/posters/left_missing.png');
                    $img = Image::make($image_path);
                    $name = 'happy.jpg';
                    $team_two_image1 = Image::make(base64_decode($team_two_image))->resize(160,160);
                    $img->insert($team_two_image1, 'bottom-left', 400, 80);
                     $this->addtext($img,base64_decode($team_two_title),455,785,12,'bottom','right','#000000',true);
                    $name = 'poster.png';
                    $this->addtext($img,$date,100,310,15,'bootom','left','#ffffff',true);
                    $this->addtext($img,$time,100, 370,12,'bootom','right','#ffffff',true);
                    $this->addtext($img,$location,100, 420,12,'bootom','right','#ffffff',true);
                    $headers = [
                        'Content-Type' => 'image/jpeg',
                        'Content-Disposition' => 'attachment; filename=' . $name,
                    ];
                    $img->encode('png');
                    return  response()->stream(function () use ($img) {
                        echo $img;
                    }, 200, $headers);
                    
                }
               if($team_one_title != null && $team_two_title === "empty")
               {
                    $image_path = public_path('images/posters/right_missing.png');
                    $img = Image::make($image_path);
                    $name = 'happy.jpg';
                    $team_one_image1 = Image::make(base64_decode($team_one_image))->resize(160,160);
                    $img->insert($team_one_image1, 'bottom-left', 42, 75);
                    $this->addtext($img,base64_decode($team_one_title), 85, 800,12,'bootom','left','#000000',true);
                    $name = 'poster.png';
                   $this->addtext($img,$date,100,310,12,'bootom','left','#ffffff',true);
                   $this->addtext($img,$time, 100, 370,12,'bootom','left','#ffffff',true);
                   $this->addtext($img,$location, 100, 420,12,'bootom','left','#ffffff',true);
                  $headers = [
                        'Content-Type' => 'image/jpeg',
                        'Content-Disposition' => 'attachment; filename=' . $name,
                    ];
                    $img->encode('png');
                    return  response()->stream(function () use ($img) {
                        echo $img;
                    }, 200, $headers);
               }
               if($team_one_title != null && $team_two_title != null)
               {
                         $image_path = public_path('images/posters/fresh.png');
                            $img = Image::make($image_path);
                            $name = 'happy.jpg';
    
                        /////////// Insert First Image ////////////
                            $team_one_image1 = Image::make(base64_decode($team_one_image))->resize(160,160);
                            $img->insert($team_one_image1, 'bottom-left', 45, 62);
                            $this->addtext($img,base64_decode($team_one_title), 85, 800,12,'bootom','left','#000000',true);
                       /////////// End Insert first Image ////////////
                      
                       /////////// Insert second Image ////////////
                       $team_two_image1 = Image::make(base64_decode($team_two_image))->resize(160,160);   
                       $img->insert($team_two_image1, 'bottom-left', 400, 65);
                       $this->addtext($img,base64_decode($team_two_title), 450, 800,12,'bootom','left','#000000',true);
                       ///////////End Insert second Image ////////////
    
                       $name = 'poster.png';
                       $this->addtext($img,$date, 100, 310,12,'bootom','left','#ffffff',true);
                       $this->addtext($img,$time, 100, 370,12,'bootom','left','#ffffff',true);
                       $this->addtext($img,$location, 100, 420,12,'bootom','left','#ffffff',true);
                       $headers = [
                        'Content-Type' => 'image/jpeg',
                        'Content-Disposition' => 'attachment; filename=' . $name,
                    ];
                    $img->encode('png');
                    return  response()->stream(function () use ($img) {
                        echo $img;
                    }, 200, $headers);
    
               }   
           }
           else
           {
            $image_path = public_path('images/posters/poster_template.png');
            $img = Image::make($image_path);
            $name = 'poster.png';
            $this->addtext($img,$date, 100, 310,15,'bootom','left','#ffffff',true);
            $this->addtext($img,$time, 100, 370,12,'bootom','left','#ffffff',true);
            $this->addtext($img,$location, 100, 420,12,'bootom','left','#ffffff',true);
            $headers = [
                'Content-Type' => 'image/jpeg',
                'Content-Disposition' => 'attachment; filename=' . $name,
            ];
            $img->encode('png');
            return  response()->stream(function () use ($img) {
                echo $img;
            }, 200, $headers);
           }
    }
    function addtext($img,$text,$x,$y,$size,$allign,$vallign,$color,$logotext = false)
    {
      $img->text($text, $x, $y, function ($font)use($size,$vallign,$allign,$color,$logotext) {
            $font->size($size);
            if($logotext)
            $font->file(public_path('fonts/FastHand/FastHand.ttf'));
            else
            $font->file(public_path('fonts/open-sans/OpenSans-ExtraBold.ttf'));
            $font->color($color);
            $font->align($allign);
            $font->valign($vallign);
            $font->angle(0);
        });
    }
    public function leagueBooking(Request $request)
    {
         // dd($request->all());
         $image_path = public_path('images/posters/league_poster.png');
       
         $img = Image::make($image_path);
      
         $name = 'match-fixtures-and-results.jpg';

         $team_two_image1 = Image::make($request->club1_image)->resize(50,50);  
         $team_two_image2 = Image::make($request->club2_image)->resize(50,50);   
         $img->insert($team_two_image1, 'top-left', 70, 45);
         $img->insert($team_two_image2, 'top-left', 70, 135);
         $this->addtext($img,$request->club1_name,155,70,12,'top','left','#ffffff');
         $this->addtext($img,$request->club2_name,155,160,12,'top','left','#ffffff');
         $this->addtext($img,$request->team_1_score,370,70,12,'top','left','#ffffff');
         $this->addtext($img,$request->team_2_score,370,160,12,'top','left','#ffffff');
         $this->addtext($img,$request->matchDayAlphabet,440,60,14,'top','right','#ffffff');
         $this->addtext($img,$request->matchDayNumeric,440,85,14,'top','right','#ffffff');
         $this->addtext($img,$request->time,150,245,14,'top','right','#ffffff');
         $this->addtext($img,$request->location,150,300,11,'top','right','#ffffff');
         $headers = [
             'Content-Type' => 'image/jpeg',
             'Content-Disposition' => 'attachment; filename=' . $name,
         ];
         $img->encode('png');
         return  response()->stream(function () use ($img) {
             echo $img;
         }, 200, $headers);
    }
    public function downloadLeagueTable($id)
    {
        $id = intval($id);
        $tournament = Tournament::with(['bookings', 'bookings.stadium', 'bookings.stadiumFacility', 'bookings.club1', 'bookings.club2', 'bookings.match'])
            ->where('status', 1)
            ->where('booking_type','league_booking')
            ->where(function ($query) {
                $query->orWhereNull('hide_frontend');
                $query->orWhere('hide_frontend', false);
            })->where('id',$id)->first();

        $teamNames = $this->uniqueTeams($id);

        $league = $this->leagueTable($tournament->bookings, $teamNames);

        $pdf = Pdf::loadView('pdf.invoice', ['league' => $league]);
//        $v = file_get_contents(url('public/invoice12.pdf'));
       
//         $im = new Imagick();
//         $im->readImage($v);
//         $im->setImageResolution(144,144);
//         $im->resampleImage  (288,288,imagick::FILTER_UNDEFINED,1);
//         $im->setImageFormat("png");
//         header("Content-Type: image/png");
// dd($im);

//         // return storage_path('invoice12.pdf');
//         $pdf = new \Spatie\PdfToImage\Pdf(public_path('invoice12.pdf'));
//         dd($pdf->getNumberOfPages());
//         $pdf->saveImage(public_path('/'));
//         dd($pdf);
//         $pdf->setResolution( 300, 300 ); 
//            $pdf->saveImage(public_path('/newImages'));
// dd("Slsl");
           
//             dd( $im);
//             $im->setResolution( 300, 300 ); 
//             $im->readImage( "Downloads/invoic.pdf" );
//         // $imgExt->readImage($pdf);
//         $img->writeImages('pdf_image_doc.jpg', true);
//         dd("Document has been converted");
        return $pdf->download('invoice.pdf');
    }
    public function tableBooking(Request $request)
    {
        $image_path = public_path('images/posters/white_table.jpg');
       
        $img = Image::make($image_path);
     
        $name = 'table_results.jpg';

        $this->addtext($img,$request->club1_name,155,70,12,'top','left','#ffffff');
        $headers = [
            'Content-Type' => 'image/jpeg',
            'Content-Disposition' => 'attachment; filename=' . $name,
        ];
        $img->encode('png');
        return  response()->stream(function () use ($img) {
            echo $img;
        }, 200, $headers);
    }
    public function leagueTable($bookings, $teamNames)
    {
        if (!empty($bookings) && count($bookings) > 0) {
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
        }

        return $teamNames;
    }
    public function contact_us()
    {
        return view('contact_us');
    }
    public function sendbasicemail() {
        $data = array('name'=>"Virat Gandhi");
     
        Mail::send(['text'=>'mail'], $data, function($message) {
            $message->to('ghulammadina2016@gmail.com', 'Tutorials Point')->subject
               ('Laravel Basic Testing Mail');
            $message->from('ghulammadina2016@gmail.com','Virat Gandhi');
         });
        echo "Basic Email Sent. Check your inbox.";
     }

    public function tournamentMatchesResults($id)
    {

        $id = intval($id);
        $tournament = Tournament::with(['bookings', 'bookings.stadium', 'bookings.stadiumFacility', 'bookings.club1', 'bookings.club2', 'bookings.match'])
            ->where('status', 1)
            ->where('booking_type','league_booking')
            ->where(function ($query) {
                $query->orWhereNull('hide_frontend');
                $query->orWhere('hide_frontend', false);
            })->where('id',$id)->first();

        $teamNames = $this->uniqueTeams($id);

        $league = $this->leagueTable($tournament->bookings, $teamNames);

   
        $upcomings = $tournament->bookings()->has('match')
            ->whereHas('club1', function ($query) {
                //                $query->where('hide_frontend', false);
            })
            ->whereHas('club2', function ($query) {
                //                $query->where('hide_frontend', false);
            })
            ->where('booking_date', ">=", Carbon::now()->format('Y-m-d'))
            ->orderBy('booking_date', 'asc')
            ->orderBy('start_time')
            ->get()->take(5);
        $bookingHistory = $tournament->bookings()->has('match')
            ->whereHas('club1', function ($query) {
                $query->where('hide_frontend', false);
            })
            ->whereHas('club2', function ($query) {
                $query->where('hide_frontend', false);
            })
            ->select('*', 'id', DB::raw('extract(month from "booking_date") as month'))
            ->with('stadiumFacility.stadium', 'club1', 'club2', 'match')
            ->orderBy('month', 'asc')
            ->orderBy('booking_date', 'asc')
            ->orderBy('start_time')
            ->get()
            ->groupBy(function ($d) {
                return Carbon::parse($d->booking_date)->format('F');
            });


        return view('front-end.pages.matches-results', [
            'tournament' => $tournament,
            'upcomings' => $upcomings,
            'bookings' => $bookingHistory,
            'tournamentLeague' => $league,
            'id' => $id,
            'isMobile' => $this->isMobile(),
        ]);
    }

    public function tournamentLeagueResults($id)
    {

        $tournament = League::orderBy('id', 'desc')->where('id', $id)->first();

//        $id = intval($id);
//        $tournament = Tournament::with(['bookings','bookings.stadium','bookings.stadiumFacility', 'bookings.club1', 'bookings.club2','bookings.match'])
//            ->where('status',1)
//            ->where(function($query){
//                $query->orWhereNull('hide_frontend');
//                $query->orWhere('hide_frontend',false);
//            })->findOrFail($id);
//
//        $upcoming = $tournament->bookings()->has('match')
//        ->whereHas('club1',function($query){
//            $query->where('hide_frontend',false);
//        })
//        ->whereHas('club2',function($query){
//            $query->where('hide_frontend',false);
//        })
//        ->where('booking_date',">=", Carbon::now()->format('Y-m-d'))
//        ->orderBy('booking_date')
//        ->orderBy('start_time')
//        ->first();
//
//        $bookings = [];
//        $bookingHistory =  $tournament->bookings()->has('match')
//        ->whereHas('club1',function($query){
//            $query->where('hide_frontend',false);
//        })
//        ->whereHas('club2',function($query){
//            $query->where('hide_frontend',false);
//        })
//        ->orderBy('booking_date')
//        ->get();
//        foreach($bookingHistory as $booking){
//                $bookings[] = $booking;
//        }
        return view('front-end.pages.matches-results', [
            'tournament' => $tournament,
//            'upcoming' => $upcoming,
//            'bookings' => $bookings,
            'isMobile' => $this->isMobile(),
        ]);
    }


    public function contactUs()
    {
        return view('front-end.pages.contact');
    }

    public function aboutUs()
    {
        $page = StaticPage::where('page_name', 'about_us')->first();
        return view('front-end.pages.about-us', [
            'page' => $page
        ]);
    }

    public function privacyPolicy()
    {
        $page = StaticPage::where('page_name', 'privacy_policy')->first();
        return view('front-end.pages.privacy-policy', [
            'page' => $page
        ]);
    }

    public function termsAndCondition()
    {
        $page = StaticPage::where('page_name', 'terms_and_conditions')->first();
        return view('front-end.pages.terms-and-conditions', [
            'page' => $page
        ]);
    }

    public function subscriptionTermsAndCondition()
    {
        $page = StaticPage::where('page_name', 'subscription_terms_and_conditions')->first();
        return view('front-end.pages.subscription-terms-and-conditions', [
            'page' => $page
        ]);
    }

    public function sponsors()
    {
        $sponsors = Sponsor::where('status', 1)->orderBy('created_at', 'desc')->get();
        return view('front-end.pages.sponsors', [
            'sponsors' => $sponsors,
        ]);
    }

    private function getToDateTimeold(Carbon $startDate, $endTime)
    {

        if ($endTime === '00:00') {
            // $endTime = '23:59';
            $startDate = clone $startDate;
            $startDate->addDays(1);
        }

        return Carbon::createFromFormat("Y-m-d H:i", $startDate->format('Y-m-d') . " " . $endTime);
    }

    private function getToDateTime($startDate, $endTime)
    {
           if($endTime === '')
           $endTime = '00:00';
        if ($endTime === '00:00') {
            // $endTime = '23:59';
            /*$startDate = clone $startDate;
            $startDate->addDays(1);*/
            $startDate = date('Y-m-d', strtotime($startDate . ' +1 day'));
        }

        return Carbon::createFromFormat("Y-m-d H:i", $startDate . " " . $endTime);
    }

    private function getFromDateTimeold(Carbon $startDate, $startTime)
    {
        return Carbon::createFromFormat("Y-m-d H:i", $startDate->format('Y-m-d') . " " . $startTime);
    }

    private function getFromDateTime($startDate, $startTime)
    {
        return Carbon::createFromFormat("Y-m-d H:i", $startDate . " " . $startTime);
    }


    public function facilities(Request $request)
    {

        if (isset($request->sport)) {
            $sport = Sport::findOrFail($request->sport);
            $courts = $sport->stadium_facilities()->where('status', 1)->where('stadium_id', $request->stadium)->orderBy('name')->first();
            return redirect()->route('book-venue', $courts->id);
        } else {
            $sports = Sport::where('status', 1)->get();
            $facilitiesBySports = [];
            $facilities = \App\StadiumFacility::with(["stadium", "sport"])->whereHas("stadium", function ($query) {
                $query->where('status', 1);
            })->where('status', 1)->get()->sortBy(function ($facility) {
                return isset($facility->sport->name) ? $facility->sport->name : '';
            });

            foreach ($facilities as $facility) {
                if (!empty($facility->sport->id)) {
                    $facilitiesBySports[$facility->sport->id][] = $facility;
                }

            }
            return view('front-end.pages.facilities', [
                'sports' => $sports,
                'facilitiesBySports' => $facilitiesBySports
            ]);
        }


    }

    function courts($stadiumId, $sportId)
    {

        $sport = Sport::findOrFail($sportId);
        $stadium = Stadium::findOrFail($stadiumId);
        $courts = $sport->stadium_facilities()->where('status', 1)->where('stadium_id', $stadiumId)->orderBy('name')->first();
//        $courts = $sport->stadium_facilities()->where('status', 1)->where('stadium_id', $stadiumId)->orderBy('name')->get();

        return redirect()->route('book-venue', $courts->id);
//        return [
//            'status' => true,
//            'sport_name' => $sport->name,
//            'stadium_name' => $stadium->name,
//            'courts' => $courts
//        ];
    }

    public function bookVenue($id)
    {
        $facility = StadiumFacility::whereId($id)->with('sport', 'stadium')->first();
        $stadiumFacilities = StadiumFacility::whereSportId($facility->sport_id)->whereStatus(1)->with(['sport', 'stadium' => function ($q) {
            $q->whereStatus(1);
        }])->get();
        $facilityImages = StadiumGallery::whereStadiumId($facility->stadium_id)->get();

        $bookingEvents = [];
        $nonOverlappingSlots = [];
        $startDate = date('Y-m-01');
        $endDate = date("Y-m-t", strtotime($startDate));

        return view('front-end.pages.book-calendar', [
            'facility' => $facility,
            'stadiumFacilities' => $stadiumFacilities,
            'facilityImages' => $facilityImages,
            'startDate' => $startDate,
            'endDate' => $endDate,
            //'bookingEvents' => $bookingEvents,
            'slots' => [...$bookingEvents, ...$nonOverlappingSlots],
            'isMobile' => $this->isMobile()
        ]);
    }

    public function ajaxBookVenue(Request $request)
    {
        $facility = StadiumFacility::findOrFail($request->facility_id);


        $admins = User::query()->where('status', 1)->get();
        $contacts = [];
        $phones = [];
        foreach ($admins as $admin) {
            $contacts[$admin->id] = $admin->name . ': ' . $admin->phone;
            $phones[$admin->id] = $admin->intl_phone;
        }


        $bookingRule = $facility->bookingRule;
//        $startDate = Carbon::now();
//        $endDate = Carbon::now()->addMonth(1);
        $startDate = date('Y-m-d', strtotime($request->startDate));
        if (date('m') == date('m', strtotime($startDate))) {
            $startDate = date('Y-m-d');
        }
        $endDate = date('Y-m-d', strtotime($request->endDate));


        $overridedPricings = OverridePricing::where('facility_id', $request->facility_id)->where('slot_date', ">=", $startDate)
            ->where('slot_date', '<=', $endDate)->get();
        $overridedPricingChecker = [];
        foreach ($overridedPricings as $op) {
            $overridedPricingChecker[$op->slot_date][$op->slot_time_start][$op->slot_time_end] = $op->overrided_price;
        }


        $weeklySchedule = [];

        if ($bookingRule !== null) {
//            $endDate = clone $startDate;
//            $endDate->addMonths($bookingRule->booking_window_duration);

            $weeklySchedule = json_decode($bookingRule->weekly_schedule, true);
        }

        $weekMap = ['Mon' => 1, 'Tue' => 2, 'Wed' => 3, 'Thu' => 4, 'Fri' => 5, 'Sat' => 6, 'Sun' => 7];

//        $loopStartDate = Carbon::now();
//        $loopTill = Carbon::now()->addMonths(1);

        $loopStartDate = $startDate;
        $loopTill = $endDate;


        $slots = [];


        while ($loopStartDate <= $loopTill) {
               //            $day = $weekMap[$loopStartDate->format('D')];
            $day = $weekMap[date('D', strtotime($loopStartDate))];
            foreach ($weeklySchedule as $schedule) {
                
                if (in_array($day, $schedule['days'])) {
                    //                if (in_array($day, $schedule['days']) && $schedule['startTime'] >= '08:00') {

                    $from = $this->getFromDateTime($loopStartDate, $schedule['startTime']);
                   if($schedule['endTime'] == '')
                   {
                         dd($schedule);
                   }
                    $to = $this->getToDateTime($loopStartDate, $schedule['endTime']);
               $date = date("j M Y", strtotime($from));
               $location = $facility->stadium->name;
               $time = $from->format('h:i A') . " - " . $to->format('h:i A');

                    $slots[] = [
                        'start' => $from->format('Y-m-d H:i:s'),
                        'end' => $to->format('Y-m-d H:i:s'),
                        // 'image' =>   $v->response('png', 70),
                        'price' => $schedule['price'],
                        'time' => $time,
                        'date' => date("j M Y", strtotime($from->format('Y-m-d'))),
                        'location' => $facility->stadium->name,
                        'contact' => $this->buildContacts($schedule["contact"], $contacts, $phones),
                        //'intl_phone'=> !empty($phones[$schedule["contact"]]) ? $phones[$schedule["contact"]]: "",
                        'title' => $from->format('h:iA') . " - " . $to->format('h:iA'),
//                       'team_one_title' => $from->format('h:iA') . " - ",
                        'team_one_title' => "???",
                        'team_one_image' => null,
//                       'team_two_title' => $to->format('h:iA'),
                        'team_two_title' => "???",
                        'team_two_image' => null,
                        'team_one_jersey_color' => null,
                        'team_two_jersey_color' => null,
                        'add_team' => 'no',
                        'only_date' => $from->format('Y-m-d'),
                        'type' => !empty($schedule['type']) ? $schedule['type'] : 'per_team',
                        'booking_status' => $this->getBookingStatusForFrontEnd($from->format('Y-m-d H:i:s')),
                        'overridedPrice' => !empty($overridedPricingChecker[$from->format('Y-m-d')][$from->format('H:i:s')][$to->format('H:i:s')]) ? $overridedPricingChecker[$from->format('Y-m-d')][$from->format('H:i:s')][$to->format('H:i:s')] : null,
                    ];
                }
            }
            $loopStartDate = date('Y-m-d', strtotime($loopStartDate . ' +1 day'));
//            $loopStartDate->addDay(1);
        }


        /// manually added slots

        $manualEndDate = $endDate;


        $loopStartDate = $startDate;

        $loopTill = $endDate;
        $bookings = Booking::whereHas('tournament', function ($query) {
            return $query->where('booking_type', 'user_booking');
        })->where('booking_type', 'user_booking')->with(['tournament' => function ($query) {
            $query->where('booking_type', 'user_booking');
        }, 'club1', 'club2', 'stadiumFacility', 'stadiumFacility.sport', 'blockBooking'])
            ->where('stadium_id', $facility->stadium->id)
            ->where('stadium_facility_id', $facility->id)
            ->where(function ($query) {
                $query->orWhere(function ($query) {
                    $query->where('fee_type', 'per_team');
                    $query->where(function ($query) {
                        $query->orWhere('club1_payment_confirmed', true);
                        $query->orWhere('club2_payment_confirmed', true);
                    });
                });
                $query->orWhere(function ($query) {
                    $query->where('fee_type', 'per_slot');
                    $query->where('slot_fee_deposit_paid', true);
                });
            })
            ->where('booking_date', '>=', $loopStartDate)
            ->where('booking_date', '<=', $loopTill)
            ->get();

        //    $img->save(asset("images/posters/". $your_image_name . ".png"));
        //    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($v);
        $bookingEvents = [];
       
        foreach ($bookings as $booking) {
            $date = date("j M Y", strtotime($booking->booking_date));
            $location = $facility->stadium->name;
            $time = date("h:i A",strtotime($booking->start_time)) . " - " . date("h:i A",strtotime($booking->end_time));
            $bookingEvents[] = [
                'start' => $booking->booking_date . " " . $booking->start_time,
                'end' => $booking->booking_date . " " . $booking->end_time,
                'price' => $booking->fee_type == 'per_team' ? $booking->club1_fee : $booking->slot_fee,
                'contact' => $this->buildContacts($booking->contact_person_id, $contacts, $phones),
                // 'image' =>   $v->response('png', 70),
                'time' => $time,
                'date' => $date,
                'location' => $facility->stadium->name,
                //'intl_phone'=> !empty($phones[$booking->contact_person_id]) ? $phones[$booking->contact_person_id]: "",
                'title' => $booking->getTitle(),
                'team_one_title' => $booking->getTitleTeamOne(),
                'team_one_image' => $booking->getImageTeamOneForPoster(),
                'team_one_image_for_show' => $booking->getImageTeamOne(),
                'team_two_title' => $booking->getTitleTeamTwo(),
                'team_two_image' => $booking->getImageTeamTwoForPoster(),
                'team_two_image_for_show' => $booking->getImageTeamTwo(),
                'add_team' => 'yes',
                'team_one_jersey_color' => $booking->club1_jersey_color ?? null,
                'team_two_jersey_color' => $booking->club2_jersey_color ?? null,
                'only_date' => $booking->booking_date,
                'booking_status' => $booking->getBookingStatusForFrontEnd(),
                'block_booking_id' => $booking->block_booking_id,
                'type' => $booking->fee_type,
                'overridedPrice' => !empty($overridedPricingChecker[$booking->booking_date][$booking->start_time][$booking->end_time]) ? $overridedPricingChecker[$booking->booking_date][$booking->start_time][$booking->end_time] : null,
            ];
        }


        $nonOverlappingSlots = [];
        foreach ($slots as $slot) {
            $overlap = false;
            foreach ($bookings as $booking) {
                $slotStart = Carbon::createFromFormat('Y-m-d H:i:s', $slot['start']);
                $slotEnd = Carbon::createFromFormat('Y-m-d H:i:s', $slot['end']);
                if ($slotEnd < $slotStart) {
                    $slotEnd->addDays(1);
                }
                $bookingStart = Carbon::createFromFormat('Y-m-d H:i:s', $booking->booking_date . " " . $booking->start_time);
                $bookingEnd = Carbon::createFromFormat('Y-m-d H:i:s', $booking->booking_date . " " . $booking->end_time);
                if ($bookingEnd < $bookingStart) {
                    $bookingEnd->addDays(1);
                }

                $result = $this->datesOverlap($slotStart, $slotEnd, $bookingStart, $bookingEnd);
                if ($result > 0) {
                    $overlap = true;
                }
            }


            if (!$overlap) {
                $nonOverlappingSlots[] = $slot;
            }
        }
        $manuallyAddedSlots = \App\ManualSlot::where('slot_date', '>=', $startDate)->where('slot_date', '<', $manualEndDate)->get();
        foreach ($manuallyAddedSlots as $manualSlot) {
            $slotStart = Carbon::createFromFormat('Y-m-d H:i:s', $manualSlot->slot_date . ' ' . $manualSlot->slot_time_start);
            $slotEnd = Carbon::createFromFormat('Y-m-d H:i:s', $manualSlot->slot_date . ' ' . $manualSlot->slot_time_end);
               $date = date("j M Y", strtotime($booking->start));
               $location = $facility->stadium->name;
               $time = date("h:i A",strtotime($slotStart)) . " - " . date("h:i A",strtotime($slotEnd));
            $nonOverlappingSlots[] = [
                'start' => $slotStart->format('Y-m-d H:i:s'),
                'end' => $slotEnd->format('Y-m-d H:i:s'),
                // 'image' =>   $img->response('png', 70),
                'price' => $manualSlot->price,
                'contact' => $this->buildContacts(explode(',', $manualSlot->contacts), $contacts, $phones),
                'contactIds' => $manualSlot->contacts,
                'title' => $slotStart->format('h:iA') . " - " . $slotEnd->format('h:iA'),
                'team_one_title' => "???",
                'team_one_image' => null,
                //                'team_one_title' => $slotStart->format('h:iA') . " - ",
                'team_two_title' => "???",
                'team_two_image' => null,
                //                'team_two_title' => $slotEnd->format('h:iA'),
                'add_team' => 'no',
                'team_one_jersey_color' => null,
                'team_two_jersey_color' => null,
                'only_date' => $slotStart->format('Y-m-d'),
                'type' => $manualSlot->type,
                'booking_status' => $this->getBookingStatusForFrontEnd($slotStart),
                'overridedPrice' => null,
                'manualSlotId' => $manualSlot->id

            ];
        }
        return [
            "data" => [...$bookingEvents, ...$nonOverlappingSlots],
        ];

    }

    public function bookVenueOverridingPricing($id)
    {
        $facility = StadiumFacility::findOrFail($id);
        $admins = User::query()->where('status', 1)->get();
        $contacts = [];
        $phones = [];
        foreach ($admins as $admin) {
            $contacts[$admin->id] = $admin->name . ': ' . $admin->phone;
            $phones[$admin->id] = $admin->intl_phone;
        }


        $bookingRule = $facility->bookingRule;
        $startDate = Carbon::now();
        $endDate = Carbon::now()->addMonth(3);

        $overridedPricings = OverridePricing::where('facility_id', $id)->where('slot_date', ">=", $startDate->format('Y-m-d'))
            ->where('slot_date', '<=', $endDate->format('Y-m-d'))->get();
        $overridedPricingChecker = [];
        foreach ($overridedPricings as $op) {
            $overridedPricingChecker[$op->slot_date][$op->slot_time_start][$op->slot_time_end] = $op->overrided_price;
        }


        $weeklySchedule = [];

        if ($bookingRule !== null) {
            $endDate = clone $startDate;
            $endDate->addMonths($bookingRule->booking_window_duration);
            $weeklySchedule = json_decode($bookingRule->weekly_schedule, true);
        }

        $weekMap = ['Mon' => 1, 'Tue' => 2, 'Wed' => 3, 'Thu' => 4, 'Fri' => 5, 'Sat' => 6, 'Sun' => 7];

        $loopStartDate = Carbon::now();
        $loopTill = clone $endDate;

        $slots = [];
        while ($loopStartDate <= $loopTill) {
            $day = $weekMap[$loopStartDate->format('D')];
            foreach ($weeklySchedule as $schedule) {
                if (in_array($day, $schedule['days'])) {

                    $from = $this->getFromDateTimeold($loopStartDate, $schedule['startTime']);

                    $to = $this->getFromDateTimeold($loopStartDate, $schedule['endTime']);

                    $slots[] = [
                        'start' => $from->format('Y-m-d H:i:s'),
                        'end' => $to->format('Y-m-d H:i:s'),
                        'price' => $schedule['price'],
                        'contact' => $this->buildContacts($schedule["contact"], $contacts, $phones),
                        //'intl_phone'=> !empty($phones[$schedule["contact"]]) ? $phones[$schedule["contact"]]: "",
                        'title' => $from->format('h:iA') . " - " . $to->format('h:iA'),
                        'only_date' => $from->format('Y-m-d'),
                        'type' => !empty($schedule['type']) ? $schedule['type'] : 'per_team',
                        'booking_status' => $this->getBookingStatusForFrontEnd($from->format('Y-m-d H:i:s')),
                        'overridedPrice' => !empty($overridedPricingChecker[$from->format('Y-m-d')][$from->format('H:i:s')][$to->format('H:i:s')]) ? $overridedPricingChecker[$from->format('Y-m-d')][$from->format('H:i:s')][$to->format('H:i:s')] : null,
                    ];
                }
            }
            $loopStartDate->addDay(1);
        }


        $loopStartDate = Carbon::now();
        $loopTill = clone $endDate;

        $bookings = Booking::whereHas('tournament', function ($query) {
            return $query->where('booking_type', 'user_booking');
        })->with(['club1', 'club2', 'stadiumFacility', 'stadiumFacility.sport', 'blockBooking'])
            ->where('stadium_id', $facility->stadium->id)
            ->where('stadium_facility_id', $facility->id)
            ->where(function ($query) {
                $query->orWhere(function ($query) {
                    $query->where('fee_type', 'per_team');
                    $query->where(function ($query) {
                        $query->orWhere('club1_payment_confirmed', true);
                        $query->orWhere('club2_payment_confirmed', true);
                    });
                });
                $query->orWhere(function ($query) {
                    $query->where('fee_type', 'per_slot');
                    $query->where('slot_fee_deposit_paid', true);
                });
            })
            ->where('booking_date', '>=', $loopStartDate->format('Y-m-d'))
            ->where('booking_date', '<', $loopTill->format('Y-m-d'))->get();

        $bookingEvents = [];
        foreach ($bookings as $booking) {

            $bookingEvents[] = [
                'start' => $booking->booking_date . " " . $booking->start_time,
                'end' => $booking->booking_date . " " . $booking->end_time,
                'price' => $booking->fee_type == 'per_team' ? $booking->club1_fee : $booking->slot_fee,
                'contact' => $this->buildContacts($booking->contact_person_id, $contacts, $phones),
                //'intl_phone'=> !empty($phones[$booking->contact_person_id]) ? $phones[$booking->contact_person_id]: "",
                'title' => $booking->getTitle(),
                'only_date' => $booking->booking_date,
                'booking_status' => $booking->getBookingStatusForFrontEnd(),
                'block_booking_id' => $booking->block_booking_id,
                'type' => $booking->fee_type,
                'overridedPrice' => !empty($overridedPricingChecker[$booking->booking_date][$booking->start_time][$booking->end_time]) ? $overridedPricingChecker[$booking->booking_date][$booking->start_time][$booking->end_time] : null,

            ];
        }

        $nonOverlappingSlots = [];
        foreach ($slots as $slot) {
            $overlap = false;
            foreach ($bookings as $booking) {


                $slotStart = Carbon::createFromFormat('Y-m-d H:i:s', $slot['start']);
                $slotEnd = Carbon::createFromFormat('Y-m-d H:i:s', $slot['end']);
                if ($slotEnd < $slotStart) {
                    $slotEnd->addDays(1);
                }

                $bookingStart = Carbon::createFromFormat('Y-m-d H:i:s', $booking->booking_date . " " . $booking->start_time);
                $bookingEnd = Carbon::createFromFormat('Y-m-d H:i:s', $booking->booking_date . " " . $booking->end_time);
                if ($bookingEnd < $bookingStart) {
                    $bookingEnd->addDays(1);
                }


                $result = $this->datesOverlap($slotStart, $slotEnd, $bookingStart, $bookingEnd);

                if ($result > 0) {
                    $overlap = true;
                }
            }

            if (!$overlap) {
                $nonOverlappingSlots[] = $slot;
            }
        }


        /// manually added slots

        $manuallyAddedSlots = \App\ManualSlot::where('slot_date', '>=', $startDate->format('Y-m-d'))->where('slot_date', '<', $endDate->format('Y-m-d'))->get();
        foreach ($manuallyAddedSlots as $manualSlot) {

            $slotStart = Carbon::createFromFormat('Y-m-d H:i:s', $manualSlot->slot_date . ' ' . $manualSlot->slot_time_start);
            $slotEnd = Carbon::createFromFormat('Y-m-d H:i:s', $manualSlot->slot_date . ' ' . $manualSlot->slot_time_end);
            $nonOverlappingSlots[] = [
                'start' => $slotStart->format('Y-m-d H:i:s'),
                'end' => $slotEnd->format('Y-m-d H:i:s'),
                'price' => $manualSlot->price,
                'contact' => $this->buildContacts(explode(',', $manualSlot->contacts), $contacts, $phones),
                'contactIds' => $manualSlot->contacts,
                'title' => $slotStart->format('h:iA') . " - " . $slotEnd->format('h:iA'),
                'only_date' => $slotStart->format('Y-m-d'),
                'type' => $manualSlot->type,
                'booking_status' => $this->getBookingStatusForFrontEnd($slotStart),
                'overridedPrice' => null,
                'manualSlotId' => $manualSlot->id

            ];
        }

        return view('front-end.pages.book-calendar', [
            'facility' => $facility,
            'startDate' => $startDate,
            'endDate' => $endDate,
            //'bookingEvents' => $bookingEvents,
            'slots' => [...$bookingEvents, ...$nonOverlappingSlots],
            'isMobile' => $this->isMobile()
        ]);
    }

    function isMobile()
    {
        return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
    }

    public function buildContacts($contactIds, $displayNames, $phones)
    {
        if (!is_array($contactIds)) {
            $contactIds = [$contactIds];
        }

        $contacts = [];
        foreach ($contactIds as $contactId) {
            if (!empty($displayNames[$contactId]) && !empty($phones[$contactId])) {
                $removeSpacePhoneNumber = str_replace(' ', '', $phones[$contactId]);
                $removeDashPhoneNumber = str_replace('-', '', $removeSpacePhoneNumber);
                $removePlusPhoneNumber = str_replace('+', '', $removeDashPhoneNumber);
                $phoneNumber = $removePlusPhoneNumber;
                $contacts[] = [
                    'displayName' => $displayNames[$contactId],
                    'intl_phone' => $phones[$contactId],
                    'whatsapp_intl_phone_number' => $phoneNumber,
                    'whatsapp_phone' => 'https://web.whatsapp.com/send?phone=' . str_replace(' ', '', $phones[$contactId]),
                ];
            }
        }
        return $contacts;
    }

    public function showTeam($id)
    {

        $club = Club::findOrFail($id);
        if ($club->hide_frontend) {
            abort(404);
        }

        return view('front-end.pages.team', [
            'club' => $club,
            'isMobile' => $this->isMobile()
        ]);
    }

    function datesOverlap($start_one, $end_one, $start_two, $end_two)
    {

        if ($start_one < $end_two && $end_one > $start_two) { //If the dates overlap
            return min($end_one, $end_two)->diff(max($start_two, $start_one))->days + 1; //return how many days overlap
        }

        return 0; //Return 0 if there is no overlap
    }


    function doEmail(Request $request)
    {
        // MAIL_FROM_ADDRESS
      $sendgrid_api = Config::get('app.sendgrid_api');
    $mail_from = Config::get('app.mail_from')['address'];
    $name = Config::get('app.mail_from')['name'];
        // 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
        // $data = array('name'=>"Virat Gandhi");
        // Mail::send(['text'=>'mail'], $data, function($message) use($request) {
        //     $message->to('ghulammadina2016@gmail.com', 'Tutorials Point')->subject
        //        ('Laravel Basic Testing Mail');
        //     $message->from($request->email,'Virat Gandhi');
        //  });
        // Mail::to('ghulammadina2016@gmail.com')->send(new ContactUs($request->name, $request->email, $request->message));
    //    Mail::to(env('ADMIN_EMAIL', 'rasikh.mashhadi@gmail.com'))->send(new ContactUs($request->name, $request->email, $request->message));
    $email = new \SendGrid\Mail\Mail(); 
    $email->setFrom($mail_from, $name);
    $email->setSubject("Sending with SendGrid is Fun");
    $email->addTo($request->email,$request->name);
    $email->addContent("text/plain", "and easy to do anywhere, even with PHP");
    $email->addContent(
        "text/html", "<strong>and easy to do anywhere, even with PHP</strong>"
    );
  
    $sendgrid = new \SendGrid($sendgrid_api);
    try {
        $response = $sendgrid->send($email);
        return [
            'status' => true,
        ];
    } catch (Exception $e) {
        echo 'Caught exception: '. $e->getMessage() ."\n";
        return [
            'status' => false,
        ];
    }    
   
    }


    function teams()
    {
        $clubs = \App\Club::where('hide_frontend', false)->orderBy('created_at', 'desc')->take(100)->get();


        return view('front-end.pages.teams', [
            'clubs' => $clubs,
        ]);
    }

    public function getBookingStatusForFrontEnd($start_time)
    {
        // if a booking is in past just show it in red booked
        if ($start_time < Carbon::now()) {
            return 3;
        } else {
            return 0;
        }
    }


}
