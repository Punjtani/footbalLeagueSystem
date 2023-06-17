<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactUs extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $email;
    public $messageBody;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $email, $message)
    {
        $this->name = $name;
        $this->email = $email;
        $this->messageBody = $message;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = "{$this->name}<{$this->email}>";
        return $this->subject("Enquiry Received From - {$subject}")->view('emails.contact-us');
    }
}
