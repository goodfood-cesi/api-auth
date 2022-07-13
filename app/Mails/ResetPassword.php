<?php

namespace App\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPassword extends Mailable {
    use Queueable, SerializesModels;

    public object $user;

    public function __construct($user) {
        $this->user = $user;
    }

    public function build(): ResetPassword {
        return $this
            ->markdown('emails.reset', ['user' => $this->user])
            ->subject('GoodFood - Reset your password');
    }
}
