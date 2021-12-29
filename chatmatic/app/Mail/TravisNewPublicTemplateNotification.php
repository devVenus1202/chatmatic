<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class TravisNewPublicTemplateNotification extends Mailable
{
    use Queueable, SerializesModels;
    public $template_uid;
    public $template_owner;
    public $template_name;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($template_uid, $template_owner, $template_name)
    {
        $this->template_uid = $template_uid;
        $this->template_owner = $template_owner;
        $this->template_name = $template_name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('templates.public_notification');
    }
}
