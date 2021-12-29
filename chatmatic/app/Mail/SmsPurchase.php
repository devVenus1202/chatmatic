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

    // https://codigofacilito.com/articulos/laravel-sendgrid

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name,$plan)
    {
        $this->name     = $name;
        $this->plan     = $plan;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {   
        return $this->markdown('sms.billing');
    }
}
