@extends('partials.patientUI')

<?php

if ( ! function_exists('checkIfExists')) {
    //check if exists
    function checkIfExists(
        $arr,
        $val
    ) {
        if (isset($arr[$val])) {
            return $arr[$val];
        }

        return '';
    }
}

$today = \Carbon\Carbon::now()->toFormattedDateString();
// $provider = CircleLinkHealth\Customer\Entities\User::find($patient->getLeadContactID());

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
        @push('styles')
            <style type="text/css">

                div.address {
                    line-height: 1.1em;
                    font-family: 'Roboto', sans-serif;
                }

                div.breakhere {
                    page-break-after: always;
                    /*height: 100%;*/
                }

                .address-height-print {
                    height: 1in !important;
                    max-height: 1in !important;
                }

                .sender-address-print {
                    font-size: 16px !important;
                }

                .receiver-address-print {
                    font-size: 16px !important;
                    height: 1in !important;
                }

                .receiver-address-padding {
                    padding-top: 1.7in !important;
                    margin-top: 0 !important;
                    margin-bottom: 0 !important;
                }

                .welcome-copy {
                    font-size: 24px;
                    margin-top: 0.5in !important;
                }

                .omr-bar {
                    height: 15px;
                    background-color: black;
                    width: 35%;
                    margin-left: 120%;
                    margin-top: 15%;
                }

                /** begin general careplan styles */

                .color-blue {
                    color: #109ace;
                }

                .font-22 {
                    font-size: 22px;
                }

                .font-18 {
                    font-size: 18px;
                }

                .top-10 {
                    margin-top: 10px;
                }

                .top-20 {
                    margin-top: 20px !important;
                }

                li.list-square {
                    list-style-type: square;
                }

                .label-primary {
                    background-color: #109ace;
                }

                .label-secondary {
                    background-color: #47beab;
                }
            </style>
        @endpush
        <div class="container">
            <section class="patient-summary">
                <div class="patient-info__main">
                    @include('partials.carePlans.patient-general-info')
                </div>

                @include('partials.carePlans.careplan-sections')

                    <!-- /OTHER INFORMATION -->
            </section>
        </div>
        <div class="row pb-before"></div>

        @push('styles')
            <script>
                var careplan = (<?php
                    echo json_encode($data);
                    ?>) || {}
            </script>
        @endpush
    @endforeach
@stop
