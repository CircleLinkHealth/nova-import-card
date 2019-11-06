@extends('partials.patientUI')

<?php
$today = \Carbon\Carbon::now()->toFormattedDateString();
?>
@section('title', 'Care Plan View/Print')
@section('activity', 'Care Plan View/Print')
@section('content')
    @foreach($careplans as $id => $careplan)
        <?php
        $patient       = \CircleLinkHealth\Customer\Entities\User::find($id);
        $billingDoctor = $patient->billingProviderUser();
        $regularDoctor = $patient->regularDoctorUser();
        ?>
        <div class="container">
            <section class="patient-summary">
                <div class="patient-info__main">
                    @include('partials.carePlans.patient-general-info')
                </div>
                @include('partials.carePlans.careplan-sections')
            </section>
        </div>
        <div class="row pb-before"></div>

    @endforeach
@stop
