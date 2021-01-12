@extends('partials.providerUI')

@section('title', 'Two Factor Authentication')
@section('activity', 'Two Factor Authentication')

<?php
/** @var \CircleLinkHealth\Customer\Entities\User $userAllInfo */
$userAllInfo = auth()->user();
$user        = [
    'authy_user'  => $userAllInfo->authyUser,
    'global_role' => $userAllInfo->global_role,
    'program_id'  => $userAllInfo->program_id,
];
?>


@section('content')

    <div class="container container--menu">
        <div class="row">
            <div class="col-xs-12 col-sm-8 col-md-4 col-sm-offset-2 col-md-offset-4">
                <user-account-settings :user="{{json_encode($user)}}"
                                       :force-enable="{{json_encode(isTwoFaEnabledForPractice($user['program_id']))}}">
                </user-account-settings>
            </div>
        </div>
    </div>
@endsection