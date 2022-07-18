<?php

namespace App\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordUpdated extends Mailable {
    use Queueable, SerializesModels;

    public object $user;

    public function __construct($user) {
        $this->user = $user;
    }

    public function build(): PasswordUpdated {
        return $this
            ->markdown('emails.passwordupdated', ['user' => $this->user])
            ->subject('GoodFood - Your password has been changed !');
    }
}
