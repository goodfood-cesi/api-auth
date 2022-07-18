<?php

namespace App\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrationSuccessful extends Mailable {
    use Queueable, SerializesModels;

    public object $user;

    public function __construct($user) {
        $this->user = $user;
    }

    public function build(): RegistrationSuccessful {
        return $this
            ->markdown('emails.registrationsuccessful', ['user' => $this->user])
            ->subject($this->user->firstname . ', welcome on board !');
    }
}
