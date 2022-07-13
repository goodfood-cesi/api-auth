@component('mail::message')
    <center><img src="https://i.imgur.com/rlPBGQz.png" alt="Logo"></center>
<h1>
    Hello {{ $user->firstname }} !
</h1>
<p>
    You forgot your password? No problem!
    Just click the button below to reset your password.
</p>

@component('mail::button', ['url' => 'http://ksu.li/account/password/'.$user->reset_password])
    CLICK HERE TO CHANGE YOUR PASSWORD
@endcomponent

Thanks,
<br>
{{ config('app.name') }}
@endcomponent
