<?php

namespace App\Http\Controllers;


use App\Booking;
use App\Membership;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DaveJamesMiller\Breadcrumbs\Facades\Breadcrumbs;

class DashboardController extends BaseController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show the application dashboard.
     *
     * @return Renderable
     */
    public function index(Request $request)
    {
        $startDate = Carbon::now()->startOfWeek(Carbon::SUNDAY)->format('Y-m-d');
        $endDate = Carbon::now()->endOfWeek(Carbon::SATURDAY)->format('Y-m-d');


        if ($request->startDate) {
            $startDate = $request->startDate;
        }

        if ($request->endDate) {
            $endDate = $request->endDate;
        }


        $bookings = Booking::with(['contactPerson', 'club1', 'club2', 'stadium', 'stadiumFacility', 'tournament'])
            ->where('booking_date', '>=', $startDate)
            ->where('booking_date', '<=', $endDate)
            ->whereNull('block_booking_id')
            ->get();

        $collectedPaymentSum = 0;
        $pendingPaymentSum = 0;
        $fullyCollectedCount = 0;
        $lookingForOppCount = 0;
        $matchCount = 0;
        $uncollectedCount = 0;
        $membershipExpiringCount =  Membership::where('status',1)->whereBetween('expires_at',[$startDate,$endDate])->count();

        $bookingUsers = [];
        $bookingCountByUser = [];
        $bookingStadiums = [];
        $bookingCountByStadium = [];
        $bookingSports = [];
        $bookingCountBySports = [];

        foreach ($bookings as $booking) {
            $collectedPaymentSum += $booking->collectedPayment();
            $pendingPaymentSum += $booking->pendingPayment();
            switch ($booking->bookingStatusX()) {
                case 'Collected':
                    $fullyCollectedCount++;
                    break;
                case 'Match':
                    $matchCount++;
                    break;
                case 'Looking For Opponent':
                    $lookingForOppCount++;
                    break;
                case 'Uncollected':
                    $uncollectedCount++;
                    break;
            }

            $bookingUsers[$booking->contactPerson->id] = $booking->contactPerson;
            if (empty($bookingCountByUser[$booking->contactPerson->id])) {
                $bookingCountByUser[$booking->contactPerson->id] = 0;
            }
            $bookingCountByUser[$booking->contactPerson->id]++;

            $bookingStadiums[$booking->stadium->id] = $booking->stadium;
            if (empty($bookingCountByStadium[$booking->stadium->id])) {
                $bookingCountByStadium[$booking->stadium->id] = 0;
            }
            $bookingCountByStadium[$booking->stadium->id]++;

            $bookingSports[$booking->stadiumFacility->sport->id] = $booking->stadiumFacility->sport;
            if (empty($bookingCountBySports[$booking->stadiumFacility->sport->id])) {
                $bookingCountBySports[$booking->stadiumFacility->sport->id] = 0;
            }
            $bookingCountBySports[$booking->stadiumFacility->sport->id]++;

        }




        return view('pages.dashboard', [
            'breadcrumbs' => Breadcrumbs::generate('dashboard'),
            'bookingCount' => count($bookings),
            'startDate' => $startDate,
            'endDate' => $endDate,
            'pendingPaymentSum' => $pendingPaymentSum,
            'collectedPaymentSum' => $collectedPaymentSum,
            'fullyCollectedCount' => $fullyCollectedCount,
            'matchCount' => $matchCount,
            'lookingForOppCount' => $lookingForOppCount,
            'uncollectedCount' => $uncollectedCount,
            'bookingUsers' => $bookingUsers,
            'bookingStadiums' => $bookingStadiums,
            'bookingCountByUser' => $bookingCountByUser,
            'bookingCountByStadium' => $bookingCountByStadium,
            'bookingSports' => $bookingSports,
            'bookingCountBySports' => $bookingCountBySports,
            'membershipExpiringCount'=>$membershipExpiringCount
        ]);
    }

    /**
     * @inheritDoc
     */
    public function create()
    {
        // TODO: Implement create() method.
    }

    /**
     * @inheritDoc
     */
    public function store(Request $request)
    {
        // TODO: Implement store() method.
    }

    /**
     * @inheritDoc
     */
    public function show($id)
    {
        // TODO: Implement show() method.
    }

    /**
     * @inheritDoc
     */
    public function edit($id)
    {
        // TODO: Implement edit() method.
    }

    /**
     * @inheritDoc
     */
    public function update(Request $request, $id)
    {
        // TODO: Implement update() method.
    }

    /**
     * @inheritDoc
     */
    public function destroy($id)
    {
        // TODO: Implement destroy() method.
    }

    /**
     *
     */
    public function redirect_to_home()
    {
        return redirect(route('dashboard'));
    }
}
