<?php

namespace App\Observers;

use App\Booking;
use App\Mail\BookingConfirmed;
use App\Match;
use Illuminate\Support\Facades\Mail;


class BookingObserver
{

    public function created(Booking  $booking)
    {
            $this->createMatchIfNotExists($booking);
            $this->generateReceipt($booking);
    }


    public function updated(Booking  $booking)
    {
        $this->createMatchIfNotExists($booking);
        $this->generateReceipt($booking);
    }


    public function deleted(Booking  $booking)
    {
        //
    }

    public function restored(Booking  $booking)
    {
        //
    }

    public function forceDeleted(Booking  $booking)
    {
        //
    }

    public function createMatchIfNotExists($booking){
            if(empty($booking->match) && $booking->club1_payment_confirmed && $booking->club2_payment_confirmed){
                $match = new Match();
                $match->booking_id = $booking->id;
                $match->save();
            }
    }

    public function generateReceipt($booking){
        if($booking->is_receipt_gen){
            // already generated
            return true;
        }

        if($booking->fee_type === 'per_team' && $booking->club1_payment_confirmed && $booking->club2_payment_confirmed){
           // generate and update booking without raising updated event
           Mail::to('no-reply@footballhub.com.my')->send(new BookingConfirmed($booking));
           Booking::withoutEvents(function () use ($booking){
                $booking->is_receipt_gen = true;
                $booking->save();
                return $booking;
            });
        }

        if($booking->fee_type === 'per_slot' && $booking->slot_fee_deposit_paid){

            Mail::to('no-reply@footballhub.com.my')->send(new BookingConfirmed($booking));
            Booking::withoutEvents(function () use ($booking){
                $booking->is_receipt_gen = true;
                $booking->save();
                return $booking;
            });
        }
    }
}
