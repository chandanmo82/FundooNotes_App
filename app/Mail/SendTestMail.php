<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendTestMail extends Mailable
{
    use Queueable, SerializesModels;
    public $name;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build($email,$data,$currentUserEmail)
    {
        $name = 'Chandan Kumar';
        $email = $email;
        $subject = 'Note shared with you:';
        $data = $name.' shared a Note with you <br>'.$data;
        return $data;
    }
}
