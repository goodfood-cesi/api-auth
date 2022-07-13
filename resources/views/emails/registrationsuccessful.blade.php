@component('mail::message')
    <center><img src="https://i.imgur.com/rlPBGQz.png" alt="Logo"></center>
<h1>
    Hello {{ $user->firstname }} !
</h1>
<p>
    Your registration has been successful.
    You can now log in to your account.
</p>

@component('mail::button', ['url' => 'http://ksu.li/account/login'])
    CLICK HERE TO LOG IN TO YOUR ACCOUNT
@endcomponent

Thanks,
<br>
{{ config('app.name') }}
@endcomponent
