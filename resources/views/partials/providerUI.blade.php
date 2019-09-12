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
     * other times, it is an instance of \CircleLinkHealth\Customer\Entities\Patient::class
     * We have to make sure that $user is always an instance of User::class by deriving it from $patient.
     */
    $user = null;
    if (isset($patient)) {
        if (is_a($patient, \CircleLinkHealth\Customer\Entities\Patient::class)) {
            $user = $patient->user;
        } else {
            $user = $patient;
        }
    }
    ?>
    <open-modal></open-modal>
    <notifications ref="globalNotification"></notifications>
@endsection