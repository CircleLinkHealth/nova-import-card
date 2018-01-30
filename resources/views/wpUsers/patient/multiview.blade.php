@extends('partials.providerUI')

<?php
/**
* Could generate careplan in HTML or PDF
* https://cpm-web.dev/manage-patients/careplan-print-multi?letter&users={patientId}
*/

use \Illuminate\Support\Collection;

if (!function_exists('checkIfExists')) {
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
// $provider = App\User::find($patient->getLeadContactIDAttribute());

?>

@section('title', 'Care Plan View/Print')
@section('activity', 'Care Plan View/Print')
@section('content')
    @foreach($careplans as $id => $careplan)
        <?php
        $patient = App\User::find($id);
        $billing = $patient->billingProviderUser();
        $lead = $patient->leadContact();
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

                .top-10 {
                    margin-top: 10px;
                }

                .top-20 {
                    margin-top: 20px;
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
                                                        @if($billing)
                                                            @if($billing->fullName){{$billing->fullName}}@endif
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
                                                        <div class="col-xs-12 address">{{strtoupper($patient->fullName)}}</div>
                                                        <div class="col-xs-12 address">{{strtoupper($patient->address)}}</div>
                                                        <div class="col-xs-12 address"> {{strtoupper($patient->city)}}
                                                            , {{strtoupper($patient->state)}} {{strtoupper($patient->zip)}}</div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-4 text-right">
                                                    <br>
                                                    <?= date("F d, Y") ?>
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
                                        Welcome to Dr. {{$billing->fullName}}'s Personalized Care Management program!
                                    </div>
                                    <br>
                                    <div class="row gutter" style="line-height: 1.0em;">
                                        We are happy you have decided to enroll in this invite-only program for
                                        continued health.
                                    </div>
                                    <br>
                                    <div class="row gutter" style="line-height: 1.0em;">
                                        As Dr. {{$billing->fullName}} mentioned, this program is an important part of
                                        better
                                        self-management of your health. By participating, you benefit in a number ways:
                                    </div>
                                    <div class="row gutter"><BR>
                                        <ul type="disc" style="line-height: 1.0em;list-style-type: disc;">
                                            <li style="list-style-type: disc;margin: 15px 0;">Regular calls to check-in
                                                on behalf of Dr. {{$billing->fullName}}, so (s)he can help keep you
                                                healthy between visits

                                            </li>
                                            <li style="list-style-type: disc;margin: 15px 0;">Avoid the inconvenience of
                                                frequent office visits and co-pays by using this program's remote care
                                            </li>
                                            <li style="list-style-type: disc;margin: 15px 0;">All of the information
                                                gathered will be available to your doctor and will allow them to see how
                                                you are doing even when you are not in their office
                                            </li>
                                            <li style="list-style-type: disc;margin: 5px 0;">Help you take better care
                                                of yourself by staying connected to your care team and doctor
                                            </li>
                                        </ul>
                                    </div>
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
                                        Linda Warshavsky
                                    </div>
                                    <div class="row gutter">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="breakhere"></div>
                        <!-- <div class="row pb-before" style="color:white;">This page left intentionally blank</div> -->
                    @endif
                    <div class="row gutter">
                        <div class="col-xs-7">
                            <h1 class="patient-summary__title patient-summary__title_9 patient-summary--careplan">Care
                                Plan</h1>
                        </div>

                        @include('partials.carePlans.approval-box')
                    </div>

                    <br>

                    <div class="row gutter">
                        <div class="col-xs-4 col-md-4 print-row text-bold">{{$patient->fullName}}</div>
                        <div class="col-xs-4 col-md-4 print-row">{{$patient->phone}}</div>
                        <div class="col-xs-4 col-md-4 print-row text-right">{{$today}}</div>
                    </div>
                    <div class="row gutter">
                        <div class="col-xs-4 col-md-4 print-row text-bold">
                            @if($billing)
                                {{$billing->fullName}} {!! ($billing->getSpecialtyAttribute() == '')? '' :  "<br> {$billing->getSpecialtyAttribute()}"!!}
                            @else
                                <em>No Billing Provider Selected</em>
                            @endif
                        </div>
                        <div class="col-xs-4 col-md-4 print-row">
                            @if($billing)
                                {{$billing->phone}}
                            @endif
                        </div>
                        <div class="col-xs-4 col-md-4 print-row text-bold text-right">{{$patient->getPreferredLocationName()}}</div>
                    </div>
                </div>
                <?php
                    $cpmProblems = new Collection($data['cpmProblems']);
                    $ccdProblems = new Collection($data['ccdProblems']);
                    $healthGoals = new Collection($data['healthGoals']);
                    $baseGoals = new Collection($data['baseHealthGoals']);
                    $healthNote = $data['healthGoalNote'];
                ?>
                <!-- CARE AREAS -->
                <div class="patient-info__subareas">
                    <?php
                        $cpmProblemsForListing = $cpmProblems->groupBy('name')->values()->map(function ($problems) {
                            return $problems->first();
                        });

                        $ccdMonitoredProblems = $ccdProblems->filter(function ($problem) use ($cpmProblems) {
                            return !$cpmProblems->first(function ($cpm) use ($problem) {
                                return $cpm['name'] == $problem['name'];
                            }) && $problem['is_monitored'];
                        })->groupBy('name')->values()->map(function ($problems) {
                            return $problems->first();
                        });
                        
                        $ccdProblemsForListing = $ccdProblems->filter(function ($problem) use ($cpmProblems) {
                            return !$problem['is_monitored'] && !$cpmProblems->first(function ($cpm) use ($problem) {
                                return $cpm['name'] == $problem['name'] || $cpm['id'] == $problem['id'];
                            });
                        })->groupBy('name')->values()->map(function ($problems) {
                            return $problems->first();
                        });
                    ?>
                    <div class="row">
                        <div class="col-xs-12">
                            <h2 class="patient-summary__subtitles patient-summary--careplan-background">We Are
                                Managing</h2>
                        </div>
                    </div>
                    <div class="row gutter">
                        <div class="col-xs-12">
                            @if (!$cpmProblemsForListing->count() && !$ccdMonitoredProblems->count()) 
                                <div class="text-center">No Problems at this time</div>
                            @else
                                <ul class="row">
                                    @foreach ($cpmProblemsForListing as $problem)
                                        <li class='top-10 col-sm-6'>
                                            {{$problem['name']}}
                                        </li>
                                    @endforeach
                                    @foreach ($ccdMonitoredProblems as $problem)
                                        <li class='top-10 col-sm-6'>
                                            {{$problem['name']}}
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        <div class="col-xs-12" v-if="ccdProblemsForListing.length > 0">
                            <h2 class="color-blue">Other Conditions</h2>
                            <ul class="row">
                                @foreach ($ccdProblemsForListing as $problem)
                                    <li class='top-10 col-sm-6'>
                                        {{$problem['name']}}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- /CARE AREAS -->
                <!-- BIOMETRICS -->
                <div class="patient-info__subareas">
                    <?php
                        $healthGoalsForListing = $healthGoals->filter(function ($goal) {
                            return $goal['enabled'];
                        })->map(function ($goal) {
                            $start = $goal['info']['starting'];
                            $start = (int)($start ? explode('/', $start)[0] : 0);
                            $end = $goal['info']['target'];
                            $end = (int)($end ? explode('/', $end)[0] : 0);

                            $goal['verb'] = ($start > $end) ? 'Decrease' : 
                                            (($goal['name'] == 'Blood Pressure' && $start > 90) ||
                                            ($start > 0 && $start < $end)) ? 'Increase' :
                                            'Regulate';
                            $goal['action'] = $goal['verb'] == 'Regulate' ? 'keep under' : 'to';
                            return $goal;
                        });
                    ?>
                    <div class="row">
                        <div class="col-xs-12">
                            <h2 class="patient-summary__subtitles patient-summary--careplan-background">Your Health
                                Goals</h2>
                        </div>
                    </div>
                    @if ($healthNote)
                        <div class="col-xs-12">
                            {{ $healthNote['body'] }}
                        </div>
                    @endif
                    <div class="row">
                            @if (!$healthGoalsForListing->count()) 
                                <div class="text-center">No Health Goals at this time</div>
                            @else
                                <ul class="subareas__list">
                                    <li class="subareas__item subareas__item--wide col-sm-12">
                                        @foreach($healthGoalsForListing as $goal)
                                            <div class="col-xs-5 print-row text-bold">{{ $goal['verb'] }} {{$goal['name']}}</div>
                                            <div class="col-xs-4 print-row text-bold">{{ $goal['action'] }} {{ $goal['info']['target'] }} {{$goal['unit']}}</div>
                                            <div class="col-xs-3 print-row">from {{ $goal['info']['starting'] }} {{$goal['unit']}}</div>
                                        @endforeach
                                    </li>
                                </ul>
                            @endif
                    </div>
                </div>
            <!-- /BIOMETRICS -->

                <!-- MEDICATIONS -->
                <div class="patient-info__subareas">
                    <div class="row">
                        <div class="col-xs-12">
                            <h2 class="patient-summary__subtitles patient-summary--careplan-background">Medications</h2>
                        </div>
                        <div class="col-xs-10">
                            <ul><strong>Monitoring these Medications</strong><BR>
                                @if(!empty($careplan['medications']))
                                    @if(is_array($careplan['medications']))
                                        @foreach($careplan['medications'] as $medi)
                                            <li style="margin-top:14px;">{!! $medi !!}</li>
                                        @endforeach
                                    @else
                                        {{$careplan['medications']}}
                                    @endif
                                @endif
                            </ul>
                        </div>
                        <div class="col-xs-10">
                            <ul><strong>Taking these Medications</strong><BR>
                                @if(!empty($careplan['taking_meds']))
                                    @if(is_array($careplan['taking_meds']))
                                        @foreach($careplan['taking_meds'] as $medi)
                                            <li style="margin:14px 0px 0px 0px;">{!! $medi !!}</li>
                                        @endforeach
                                    @else
                                        {{$careplan['taking_meds']}}
                                    @endif
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- /MEDICATIONS -->

                <!-- SYMPTOMS -->
                <div class="patient-info__subareas">
                    <div class="row">
                        <div class="col-xs-12">
                            <h2 class="patient-summary__subtitles patient-summary--careplan-background">Watch out
                                for:</h2>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12">
                            <ul class="subareas__list">
                                @foreach($careplan['symptoms'] as $s)
                                    <li class='subareas__item inline-block col-xs-6 col-sm-4 print-row'>{{$s}}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- /SYMPTOMS -->

                <!-- LIFESTYLES -->
                <div class="patient-info__subareas">
                    <div class="row">
                        <div class="col-xs-12">
                            <h2 class="patient-summary__subtitles patient-summary--careplan-background">We Are Informing
                                You
                                About</h2>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12">
                            <ul class="subareas__list">
                                @foreach($careplan['lifestyle'] as $style)
                                    <li class='subareas__item inline-block col-xs-6 col-sm-3 print-row'>{{$style}}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- /LIFESTYLES -->


                <!-- INSTRUCTIONS -->
                <div class="patient-info__subareas pb-before">
                    <div class="row">
                        <div class="col-xs-12 print-only">
                            <h1 class="patient-summary__title patient-summary__title_9 patient-summary--careplan">Care
                                Plan
                                Part 2</h1>
                        </div>

                        <div class="col-xs-12">
                            <h2 class="patient-summary__subtitles patient-summary--careplan-background">Check In
                                Plan</h2>
                        </div>

                        <div class="col-xs-12">
                            <p>Your care team will check in with you at {{$patient->phone}} periodically.</p>
                        </div>
                        <div class="col-xs-12">
                            <h2 class="patient-summary__subtitles patient-summary--careplan-background">Follow these
                                Instructions:</h2>
                        </div>
                        <div class="col-xs-12">
                            <p></p>
                        </div>
                    </div>
                </div>

                @include('partials.view-care-plan.followTheseInstructions', [
                    'problems' => $careplan['problems']
                ])

            <!-- OTHER INFORMATION -->
                <div class="row pb-before">
                    <div class="col-xs-12 print-only">
                        <h1 class="patient-summary__title patient-summary__title_9  patient-summary--careplan">Care Plan
                            Part 3</h1>
                    </div>
                    <div class="col-xs-12">
                        <h1 class="patient-summary__title--secondary patient-summary--careplan"><p>Other information</p>
                        </h1>
                    </div>
                </div>

                <!-- ALLERGIES -->
                <div class="patient-info__subareas">
                    <div class="row">
                        <div class="col-xs-12">
                            <h2 class="patient-summary__subtitles patient-summary--careplan-background">Allergies:</h2>
                        </div>
                        <div class="col-xs-12">
                            @if($careplan['allergies'])
                                <p><?= nl2br($careplan['allergies']) ?></p>
                            @else
                                <p>No instructions at this time</p>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- /ALLERGIES -->

                <!-- SOCIALSERVICES -->
                <div class="patient-info__subareas">
                    <div class="row">
                        <div class="col-xs-12">
                            <h2 class="patient-summary__subtitles patient-summary--careplan-background">Social
                                Services:</h2>
                        </div>
                        <div class="col-xs-12">
                            @if($careplan['social'])
                                <p><?= nl2br($careplan['social']) ?></p>
                            @else
                                <p>No instructions at this time</p>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- /SOCIAL AND OTHER SERVICES -->

                <!-- CARE TEAM -->
                <div class="patient-info__subareas">
                    <div class="row">
                        <div class="col-xs-12">
                            <h2 class="patient-summary__subtitles patient-summary--careplan-background">Care Team:</h2>
                        </div>
                        <div class="col-xs-12">
                            <ul class="col-xs-12">
                                @foreach($careTeam as $carePerson)
                                    <li class="col-xs-12">
                                        <div class="col-md-7">
                                            <p style="margin-left: -10px;">
                                                <strong>
                                                    {{snakeToSentenceCase($carePerson->type)}}:
                                                </strong>{{$carePerson->user->first_name}} {{$carePerson->user->last_name}}
                                            </p>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- /CARE TEAM -->
                <!-- Appointments -->
                @if(isset($careplan['appointments']['upcoming']) || isset($careplan['appointments']['past'] ))

                    <div class="patient-info__subareas">
                        <div class="row">
                            <div class="col-xs-12">
                                <h2 class="patient-summary__subtitles patient-summary--careplan-background">
                                    Appointments</h2>
                            </div>
                            <div class="col-xs-12">

                                @if(isset($careplan['appointments']['upcoming'] ))
                                    <h3 class="patient-summary__subtitles--subareas patient-summary--careplan">
                                        Upcoming: </h3>
                                    <ul style="line-height: 30px">
                                        @foreach($careplan['appointments']['upcoming'] as $upcoming)
                                            <li style="list-style: dash">

                                                - {{$upcoming['type']}}
                                                <strong>{{$upcoming['specialty']}} </strong>
                                                on {{$upcoming['date']}}
                                                at {{$upcoming['time']}} with
                                                <strong>{{$upcoming['name']}}</strong>; {{$upcoming['address']}} {{$upcoming['phone']}}

                                            </li>
                                        @endforeach
                                        @endif
                                    </ul>
                                    @if(isset($careplan['appointments']['past'] ))
                                        <h3 class="patient-summary__subtitles--subareas patient-summary--careplan">
                                            Past:</h3>
                                        <ul style="line-height: 30px">
                                            @foreach($careplan['appointments']['past'] as $past)
                                                <li style="list-style: dash">

                                                    - {{$past['type']}}
                                                    <strong>{{$past['specialty']}} </strong>
                                                    on {{$past['date']}}
                                                    at {{$past['time']}} with
                                                    <strong>{{$past['name']}}</strong>; {{$past['address']}} {{$past['phone']}}

                                                </li>
                                            @endforeach
                                            @endif
                                        </ul>
                            </div>
                        </div>
                    </div>
            @endif
            <!-- /Appointments -->

                <!-- OTHER NOTES -->
                <div class="patient-info__subareas">
                    <div class="row">
                        <div class="col-xs-12">
                            <h2 class="patient-summary__subtitles patient-summary--careplan-background">Other
                                Notes:</h2>
                        </div>
                        <div class="col-xs-12">
                            <?php $careplan['other'] ?>

                            @if($careplan['other'])
                                <p><?= nl2br($careplan['other']) ?></p>
                            @else
                                <p>No instructions at this time</p>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- /OTHER NOTES -->
                <!-- /OTHER INFORMATION -->
            </section>
        </div>
        <div class="row pb-before"></div>
        
        @push('styles')
            <script>
                var careplan = (<?php
                echo json_encode($data)
            ?>) || {}
            </script>
        @endpush
    @endforeach
@stop