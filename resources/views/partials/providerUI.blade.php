@extends('layouts.provider')

@section('app')

    @if(!isset($isPdf))
        @include('partials.providerUI.primarynav')

        @if(!empty($patient->id))
            @include('partials.providerUI.patientnav')
        @endif

        @if(!empty($patient->id))
            @include('partials.fab')
        @endif
    @endif


    @yield('content')

    <?php
    /**
     * Sometimes, $patient is an instance of User::class,
     * other times, it is an instance of Patient::class
     * We have to make sure that $user is always an instance of User::class by deriving it from $patient
     */
    use App\Patient;

    $user = null;
    if (isset($patient)) {
        if (is_a($patient, Patient::class)) {
            $user = $patient->user;
        } else {
            $user = $patient;
        }
    }
    ?>

    @if(isset($user) && auth()->check() && !isset($isPdf) && auth()->user()->shouldShowLegacyBhiBannerFor($user))
        @include('partials.providerUI.bhi-notification-banner')
    @endif

    <open-modal></open-modal>
    <notifications ref="globalNotification"></notifications>
@endsection