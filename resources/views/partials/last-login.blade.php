<?php

/** @var CircleLinkHealth\Customer\Entities\User $user */
$user      = auth()->user();
$lastLogin = null;
$timezone  = null;

$time = null;

if (session('last_login')) {
    $dt = Carbon\Carbon::parse(session('last_login'));

    if ($user->timezone) {
        $dt = $dt->setTimezone($user->timezone);
    }
    $lastLogin = $dt->format('D, M j, Y');
    $time      = $dt->toTimeString();
    $timezone  = $dt->format('T');
}
?>

@empty($lastLogin)
@else
    <li>
        <div style="text-align: center; font-style: italic; font-size:small; color:grey; padding-left:10px; padding-right:10px; padding-bottom: 5px">
            Last Login: {{$lastLogin}}
            <br/>
            {{$time}} {{$timezone}}
        </div>
    </li>
@endempty
