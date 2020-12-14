@extends('resources.views.layouts.provider')

@section('app')
    @if(!isset($isPdf))
        @include('resources.views.partials.providerUI.primarynav')

        @if(!empty($patient->id))
            @include('resources.views.partials.providerUI.patientnav')
        @endif

        @if(!empty($patient->id) && ((! auth()->user()->isCareCoach() && auth()->user()->hasPermission('note.create')) || (auth()->user()->isCareCoach() && app(CircleLinkHealth\Customer\Policies\CreateNoteForPatient::class)->can(auth()->id(), $patient->id))))
            @include('resources.views.partials.fab')
        @endif
    @endif

    <open-modal></open-modal>
    <notifications ref="globalNotification"></notifications>

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

@endsection