<?php

/** @var CircleLinkHealth\Customer\Entities\User $user */
$user      = auth()->user();
$lastLogin = null;
if ($user->last_login) {
    $dt = Carbon\Carbon::parse($user->last_login);
    if ($user->timezone) {
        $dt = $dt->setTimezone($user->timezone);
    }
    $lastLogin = $dt->toDayDateTimeString();
}
?>

@empty($lastLogin)
@else
    <li style="margin-bottom: 5px; text-align: center;">
        <div style="font-style: italic; font-size:small; color:grey;padding:3px;">
            Last Login: {{$lastLogin}}
        </div>
    </li>
@endempty