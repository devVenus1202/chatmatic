<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PageAdminNotification extends Mailable
{
    use Queueable, SerializesModels;
    public $page_name;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($page_name)
    {
        $this->page_name = $page_name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('user.admin_notification');
    }
}
