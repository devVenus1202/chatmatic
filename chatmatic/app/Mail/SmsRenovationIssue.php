<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SmsPurchase extends Mailable
{
    use Queueable, SerializesModels;
    public $name;
    public $plan;
    public $error;

    // https://codigofacilito.com/articulos/laravel-sendgrid

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name,$plan,$error)
    {
        $this->name     = $name;
        $this->plan     = $plan;
        $this->error    = $error;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {   
        return $this->view('sms.renovate_issue');
    }
}
