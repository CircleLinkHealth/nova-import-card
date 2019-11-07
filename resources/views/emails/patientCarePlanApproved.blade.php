@component('mail::layout')
@slot('header')
@component('mail::header', ['url' => config('app.url')])
CircleLink Health
@endcomponent
@endslot

{{ $body }}

@component('mail::button', ['url' => $action_url])
{{$action_text}}
@endcomponent

@if($is_followup)
Haven't setup your password yet? Click here: [Setup password]({{$reset_url}})
@endif

Regards,

CircleLink Team

@slot('subcopy')
@component('mail::subcopy')

If you're having trouble clicking the "{{$action_text}}" button, copy and past the URL below into your web browser: {{$action_url}}.

@endcomponent
@endslot
@slot('footer')
@component('mail::footer')
&copy; [CircleLink Health]({{url('/')}}) All rights reserved.
@endcomponent
@endslot
@endcomponent