<?php

namespace App\Mail;

use App\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = $this->booking->stadiumFacility->name.'-'.$this->booking->stadiumFacility->sport->name.'-'.$this->booking->start_date_time->format('dmy').'-'.$this->booking->id;
        return $this->subject("Booking Confirmed Ref# {$subject}")->view('emails.booking-confirmed');
    }
}
