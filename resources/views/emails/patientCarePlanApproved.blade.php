@component('mail::layout')
@slot('header')
@component('mail::header', ['url' => config('app.url')])
CircleLink Health
@endcomponent
@endslot

{{ $body }}

@component('mail::button', ['url' => $home_url])
Go to Care Plan Manager
@endcomponent

{{ $instructions }}

@component('mail::button', ['url' => $reset_url])
Setup/reset password
@endcomponent

Regards,

CircleLink Team

@slot('subcopy')
@component('mail::subcopy')
If you're having trouble clicking the "Go to Care Plan Manager" button, copy and past the URL below into your web browser: {{$home_url}}.

If you're having trouble clicking the "Setup/reset password button, copy and past the URL below into your web browser:{{$reset_url}}.
@endcomponent
@endslot
@slot('footer')
@component('mail::footer')
&copy; [CircleLink Health]({{url('/')}}) All rights reserved.
@endcomponent
@endslot
@endcomponent