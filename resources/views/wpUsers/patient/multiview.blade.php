@extends('partials.providerUI')

<?php
/**
 * Could generate careplan in HTML or PDF
 * https://cpm-web.dev/manage-patients/careplan-print-multi?letter&users={patientId}.
 */
use Illuminate\Support\Collection;

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
                body {
                    margin: 0;
                    margin-right: 150px !important;
                }

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
                    @if($letter)
                        <div class="patient-info__main ">
                            <div class="row gutter">
                                <div class="col-xs-12">
                                    <div class="row address-height-print">
                                        <div class="col-xs-12 sender-address-print">
                                            <div class="row">
                                                <div class="col-xs-12 address"><strong>On Behalf of</strong></div>
                                                <div class="col-xs-7 address">
                                                    <div>
                                                        @if($billingDoctor)
                                                            @if($billingDoctor->getFullName()){{$billingDoctor->getFullName()}}@endif
                                                        @endif
                                                    </div>
                                                    <div>
                                                        {{$patient->primaryPractice->display_name}}
                                                    </div>
                                                    <div>
                                                        @if($patient->getPreferredLocationAddress())
                                                            <div>{{$patient->getPreferredLocationAddress()->address_line_1}}</div>
                                                            <!-- <div class="col-xs-4 col-xs-offset-1 print-row text-right">Phone: 203 847 5890</div> -->
                                                            <div>{{$patient->getPreferredLocationAddress()->city}}
                                                                , {{$patient->getPreferredLocationAddress()->state}} {{$patient->getPreferredLocationAddress()->postal_code}}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-xs-4 col-xs-offset-1 print-row text-right">
                                                    <div>290 Harbor Drive</div>
                                                    <div>Stamford, CT 06902</div>
                                                    <div class="omr-bar"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row receiver-address-padding">
                                        <div class="col-xs-12 receiver-address-print">
                                            <div class="row">
                                                <div class="col-xs-8">
                                                    <div class="row">
                                                        <div class="col-xs-12 address">{{strtoupper($patient->getFullName())}}</div>
                                                        <div class="col-xs-12 address">{{strtoupper($patient->address)}}</div>
                                                        <div class="col-xs-12 address"> {{strtoupper($patient->city)}}
                                                            , {{strtoupper($patient->state)}} {{strtoupper($patient->zip)}}</div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-4 text-right">
                                                    <br>
                                                    <?= date('F d, Y'); ?>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row gutter">
                                <div class="col-xs-10 welcome-copy">
                                    <div class="row gutter">
                                        Dear {{ucfirst(strtolower($patient->first_name))}} {{ucfirst(strtolower($patient->last_name))}}
                                        ,
                                    </div>
                                    <div class="row gutter">
                                    </div>
                                    <div class="row gutter" style="line-height: 1.0em;">
                                        Welcome to @if('UPG' === $patient->primaryPractice->name)CircleLink Health's @else Dr. {{$billingDoctor->getFullName()}}'s @endif Personalized Care Management
                                        program!
                                    </div>
                                    <br>
                                    <div class="row gutter" style="line-height: 1.0em;">
                                        We’re happy you’ve decided to join this program so you can get the benefits of better health.
                                    </div>
                                    <br>
                                    <div class="row gutter" style="line-height: 1.0em;">
                                        Enclosed is a copy of the care plan we've discussed or will discuss soon. 
                                        Please take a moment to read it; we'll continue to refer to it throughout our coaching sessions.
                                    </div>
                                    @if($shouldShowMedicareDisclaimer)
                                        <div class="row gutter" style="line-height: 1.0em;">
                                            As a reminder this program is covered under Medicare Part B.
                                            However, some health insurance plans may charge a co-payment. You can contact
                                            your health plan if you are not sure or you can ask for assistance from your
                                            care coach when they reach out to you.
                                        </div>
                                    @endif
                                    <div class="row gutter" style="line-height: 1.0em;">
                                        Enclosed please find a copy of your personalized care plan. Please take a few
                                        minutes to review the care plan and call us if you have any questions. You can
                                        leave a message for your care team 24/7 at the following number:
                                    </div>
                                    <div class="row gutter">
                                    </div>
                                    <div class="row gutter text-bold text-center">
                                        (888) 729-4045
                                    </div>
                                    <div class="row gutter"><BR><BR>
                                    </div>
                                    <div class="row gutter">
                                        Thanks so much. We are eager to have you benefit from this worthwhile program!
                                    </div>
                                    <div class="row gutter">
                                    </div>
                                    <div class="row gutter">
                                        <br>Best,<br><br><br>
                                    </div>
                                    <div class="row gutter">
                                    </div>
                                    <div class="row gutter">
                                    </div>
                                    <div class="row gutter">
                                    </div>
                                    <div class="row gutter">
                                        Ethan Roney
                                    </div>
                                    <div class="row gutter">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="breakhere"></div>
                        <!-- <div class="row pb-before" style="color:white;">This page left intentionally blank</div> -->
                    @endif
                    @if($generatePdfCareplan)
                            @include('partials.carePlans.patient-general-info')
                    @endif
                </div>
            @if($generatePdfCareplan)
                @include('partials.carePlans.careplan-sections')
                    <!-- /OTHER INFORMATION -->
            </section>
        </div>
        <div class="row pb-before"></div>
        @endif

        @push('styles')
            <script>
                var careplan = (<?php
                    echo json_encode($data);
                    ?>) || {}
            </script>
        @endpush
    @endforeach
@stop