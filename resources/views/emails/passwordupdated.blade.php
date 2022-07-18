@component('mail::message')
    <center><img src="https://i.imgur.com/rlPBGQz.png" alt="Logo"></center>
<h1>
    Hello {{ $user->firstname }} !
</h1>
<p>
    Your password has been changed.
    You can now log in to your account.
</p>

<p>
    If you are not the one who changed your password, please contact us immediately.
</p>

Thanks,
<br>
{{ config('app.name') }}
@endcomponent
