@component('mail::layout')
@slot('header')
@component('mail::header', ['url' => ''])
{{$practice_name}}
@endcomponent
@endslot

{{ $body }}

@component('mail::button', ['url' => $action_url])
{{$action_text}}
@endcomponent

@if($is_followup)
Haven't setup your password yet? Click here: [Setup password]({!!$reset_url!!})
@endif

Regards,

{{$practice_name}}

@slot('subcopy')
@component('mail::subcopy')

If you're having trouble clicking the "{{$action_text}}" button, copy and past the URL below into your web browser: {!!$action_url!!}.

@endcomponent
@endslot
@slot('footer')
@component('mail::footer')
{{$practice_name}}
@endcomponent
@endslot
@endcomponent