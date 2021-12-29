<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class TemplatePurchase extends Mailable
{
    use Queueable, SerializesModels;
    public $name;
    public $template;
    public $code;

    // https://codigofacilito.com/articulos/laravel-sendgrid

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name,$template,$code)
    {
        $this->name     = $name;
        $this->template = $template;
        $this->code     = $code;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {   
        return $this->markdown('templates.billing');
    }
}
