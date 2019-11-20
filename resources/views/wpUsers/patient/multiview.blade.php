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
                                        Welcome to Dr. {{$billingDoctor->getFullName()}}'s Personalized Care Management
                                        program!
                                    </div>
                                    <br>
                                    <div class="row gutter" style="line-height: 1.0em;">
                                        We are happy you have decided to enroll in this invite-only program for
                                        continued health.
                                    </div>
                                    <br>
                                    <div class="row gutter" style="line-height: 1.0em;">
                                        As Dr. {{$billingDoctor->getFullName()}} mentioned, this program is an important
                                        part
                                        of
                                        better
                                        self-management of your health. By participating, you benefit in a number ways:
                                    </div>
                                    <div class="row gutter"><BR>
                                        <ul type="disc" style="line-height: 1.0em;list-style-type: disc;">
                                            <li style="list-style-type: disc;margin: 15px 0;">Regular calls to check-in
                                                on behalf of Dr. {{$billingDoctor->getFullName()}}, so (s)he can help
                                                keep
                                                you
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

                        <div class="row gutter">
                            <div class="col-xs-7">
                                <h1 class="patient-summary__title patient-summary__title_9 patient-summary--careplan">
                                    Care
                                    Plan</h1>
                            </div>

                            @include('partials.carePlans.approval-box')
                        </div>

                        <br>

                        <div class="row gutter">
                            <div class="col-xs-5 print-row text-bold">{{$patient->getFullName()}}
                                (DOB: {{$patient->patientInfo->dob()}})
                            </div>
                            <div class="col-xs-3 print-row">{{$patient->getPhone()}}</div>
                            <div class="col-xs-4 print-row text-right">{{$today}}</div>
                        </div>
                        <div class="row gutter">
                            @if($billingDoctor)
                                <div class="col-xs-5 print-row text-bold">
                                    {{$billingDoctor->getFullName()}} {!! ($billingDoctor->getSpecialty() == '')? '' :  "<br> {$billingDoctor->getSpecialty()}"!!}
                                </div>
                                <div class="col-xs-3 print-row">
                                    {{$billingDoctor->getPhone()}}
                                </div>
                            @else
                                <div class="col-xs-5 print-row text-bold">
                                    <em>No Billing Dr. Selected</em>
                                </div>
                                <div class="col-xs-3 print-row">
                                </div>
                            @endif
                            <div class="col-xs-4 print-row text-bold text-right">{{$patient->getPreferredLocationName()}}</div>
                        </div>


                        @if($regularDoctor)
                            <div class="row gutter">
                                <div class="col-xs-5 print-row text-bold">
                                    {{$regularDoctor->getFullName()}} {!! ($regularDoctor->getSpecialty() == '')? '' :  "<br> {$regularDoctor->getSpecialty()}"!!}
                                </div>
                                <div class="col-xs-3 print-row">
                                    {{$regularDoctor->getPhone()}}
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            @if($generatePdfCareplan)
                <?php
                $allCpmProblems = new Collection($data['allCpmProblems']);
                $cpmProblems    = new Collection($data['cpmProblems']);
                $ccdProblems    = new Collection($data['ccdProblems']);
                $healthGoals    = new Collection($data['healthGoals']);
                $baseGoals      = new Collection($data['baseHealthGoals']);
                $healthNote     = $data['healthGoalNote'];
                ?>
                <!-- CARE AREAS -->
                    <div class="patient-info__subareas">
                        <?php
                        $ccdProblems = $ccdProblems->map(function ($problem) use ($allCpmProblems) {
                            if ( ! $problem['instruction']) {
                                $cpmProblem = $allCpmProblems->first(function ($cpm) use ($problem) {
                                    return ($cpm['name'] == $problem['name']) || ($cpm['id'] == $problem['cpm_id']);
                                });
                                if ($cpmProblem) {
                                    $problem['instruction'] = $cpmProblem['instruction'];
                                }
                            }

                            return $problem;
                        });

                        $problemsWithInstructions = $ccdProblems->filter(function ($ccd) {
                            return $ccd['instruction']['name'];
                        });

                        $ccdMonitoredProblems = $ccdProblems->filter(function ($problem) {
                            return $problem['is_monitored'];
                        })->groupBy('name')->values()->map(function ($problems) {
                            return $problems->first();
                        });

                        $ccdProblemsForListing = $ccdProblems->filter(function ($problem) {
                            return ! $problem['is_monitored'];
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
                                @if (!$ccdMonitoredProblems->count())
                                    <div class="text-center">No Monitored Problems at this time</div>
                                @else
                                    <ul class="row">
                                        @foreach ($ccdMonitoredProblems as $problem)
                                            <li class='top-10 col-sm-6'>
                                                {{$problem['name']}}
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                            <!-- Do NOT show other conditions in PRINT. See https://github.com/CircleLinkHealth/cpm-web/issues/1871 -->
                            @if ($ccdProblemsForListing->count() > 0 && false)
                                <div class="col-xs-12">
                                    <h2 class="color-blue">Other Conditions</h2>
                                    <ul class="row">
                                        @foreach ($ccdProblemsForListing as $problem)
                                            <li class='top-10 col-sm-6'>
                                                {{$problem['name']}}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                    <!-- /CARE AREAS -->
                    <!-- BIOMETRICS -->
                    <div class="patient-info__subareas">
                        <?php
                        $healthGoalsForListing = $healthGoals->sortBy('id')->filter(function ($goal) {
                            return $goal['enabled'];
                        })->map(function ($goal) {
                            $start = $goal['info']['starting'];
                            $start = (int) ($start
                                ? explode('/', $start)[0]
                                : 'N/A');
                            $end = $goal['info']['target'];
                            $end = (int) ($end
                                ? explode('/', $end)[0]
                                : 0);

                            if ('' == $goal['info']['starting']) {
                                $goal['info']['starting'] = 'N/A';
                            }

                            if ('Blood Sugar' == $goal['name']) {
                                $goal['info']['target'] = $goal['info']['target'] ?? '120';
                                if ('0' == $goal['info']['target']) {
                                    $goal['info']['target'] = '120';
                                }
                                if ($start > 130) {
                                    $goal['verb'] = 'Decrease';
                                } elseif ('N/A' == $goal['info']['starting'] || 'TBD' == $goal['info']['target'] || ! $goal['info']['starting'] || ($start >= 80 && $start <= 130)) {
                                    $goal['verb'] = 'Regulate';
                                } else {
                                    $goal['verb'] = 'Increase';
                                }
                            } elseif ('Blood Pressure' == $goal['name']) {
                                $goal['info']['target'] = $goal['info']['target'] ?? '130/80';
                                if ('0' == $goal['info']['target']) {
                                    $goal['info']['target'] = '130/80';
                                }

                                if ('N/A' == $goal['info']['starting'] || 'TBD' == $goal['info']['target'] || ! $goal['info']['starting'] || ($start < 130)) {
                                    $goal['verb'] = 'Regulate';
                                } elseif ($start >= 130) {
                                    $goal['verb'] = 'Decrease';
                                }
                            } else {
                                if ( ! $goal['info']['starting'] || 'N/A' == $goal['info']['starting'] || ! $goal['info']['target'] || ('Weight' == $goal['name'] && '0' == $goal['info']['target'])) {
                                    if (('Weight' == $goal['name'] && '0' == $goal['info']['target'])) {
                                        $goal['info']['target'] = 'N/A';
                                    }
                                    $goal['verb'] = 'Regulate';
                                } else {
                                    $goal['verb'] = ($start > $end)
                                        ? 'Decrease'
                                        :
                                        (($start < $end)
                                            ? 'Increase'
                                            :
                                            'Regulate');
                                }
                            }
                            $goal['action'] = 'Regulate' == $goal['verb']
                                ? 'keep under'
                                : 'to';

                            return $goal;
                        });
                        ?>
                        <div class="row">
                            <div class="col-xs-12">
                                <h2 class="patient-summary__subtitles patient-summary--careplan-background">Your Health
                                    Goals</h2>
                            </div>
                        </div>
                        <div class="row">
                            <?php
                            $noteIsAvailable = $healthNote && ('' != $healthNote['body']);
                            ?>
                            @if ($noteIsAvailable)
                                <div class="col-xs-12 top-10">
                                    {{ $healthNote['body'] }}
                                </div>
                            @endif
                            @if (!$healthGoalsForListing->count())
                                <div class="col-sm-12 text-center top-20">No Health Goals at this time</div>
                            @else
                                @if ($noteIsAvailable)
                                    <br><br>
                                @endif
                                <ul class="col-sm-12 subareas__list top-20"
                                    style="{{ $noteIsAvailable ? 'padding-top:10px !important;' : '' }}">
                                    <li class="subareas__item subareas__item--wide col-sm-12">
                                        @foreach($healthGoalsForListing as $goal)
                                            <div class="col-xs-5 print-row text-bold">{{ $goal['verb'] }} {{$goal['name']}}</div>
                                            <div class="col-xs-4 print-row text-bold">{{ $goal['action'] }} {{ $goal['info']['target'] }} {{$goal['unit']}}</div>
                                            <div class="col-xs-3 print-row">
                                                from {{ $goal['info']['starting'] }} {{$goal['unit']}}</div>
                                        @endforeach
                                    </li>
                                </ul>
                            @endif
                        </div>
                    </div>
                    <!-- /BIOMETRICS -->

                    <!-- MEDICATIONS -->
                    <?php
                    $medications      = new Collection($data['medications']);
                    $medicationGroups = new Collection($data['medicationGroups']);

                    $medications = $medications
                        ->filter(function ($med) {
                            return $med['active'];
                        })
                        ->map(function ($medication) use ($medicationGroups) {
                            $medication['group'] = $medicationGroups->first(function ($group) use (
                                $medication
                            ) {
                                return $group['id'] == $medication['medication_group_id'];
                            });

                            return $medication;
                        });
                    ?>
                    <div class="patient-info__subareas">
                        <div class="row">
                            <div class="col-xs-12">
                                <h2 class="patient-summary__subtitles patient-summary--careplan-background">
                                    Medications</h2>
                            </div>
                            <div class="col-xs-12">
                                <ul v-if="medications.length">
                                    @foreach ($medications as $medication)
                                        <li class="top-10">
                                            @if ($medication['name'])
                                                <h4>{{$medication['name']}}
                                                    @if ($medication['group']['name'])
                                                        <label class="label label-secondary">{{$medication['group']['name']}}</label>
                                                    @endif
                                                </h4>
                                            @endif
                                            @if (!$medication['name'])
                                                <h4>- {{$medication['sig']}}
                                                    @if ($medication['group']['name'])
                                                        <label class="label label-primary">{{$medication['group']['name']}}</label>
                                                    @endif
                                                </h4>
                                            @endif
                                            @if ($medication['name'] && $medication['sig'])
                                                <ul class="font-18">
                                                    @foreach (explode('\n', $medication['sig']) as $sig)
                                                        <li class="list-square">{{$sig}}</li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- /MEDICATIONS -->

                    <!-- SYMPTOMS -->
                    <?php
                    $symptoms = (new Collection($data['symptoms']))->sortBy('name');
                    ?>
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
                                    @foreach($symptoms as $symptom)
                                        <li class='subareas__item inline-block col-xs-6 col-sm-4 print-row top-20'>{{$symptom['name']}}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @if (empty($symptoms))
                                <div class="col-xs-12 text-center">
                                    No Symptoms at this time
                                </div>
                            @endif
                        </div>
                    </div>
                    <!-- /SYMPTOMS -->

                    <!-- LIFESTYLES -->
                    <?php
                    $lifestyles = $data['lifestyles'];
                    ?>
                    <div class="patient-info__subareas">
                        <div class="row">
                            <div class="col-xs-12">
                                <h2 class="patient-summary__subtitles patient-summary--careplan-background">We Are
                                    Informing
                                    You
                                    About</h2>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12">
                                <ul class="subareas__list">
                                    @foreach($lifestyles as $lifestyle)
                                        <li class='subareas__item inline-block col-xs-6 col-sm-3 print-row'>{{$lifestyle['name']}}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- /LIFESTYLES -->


                    <div class="patient-info__subareas pb-before">
                        <div class="row">
                            <div class="col-xs-12">
                                <h2 class="patient-summary__subtitles patient-summary--careplan-background">Check In
                                    Plan</h2>
                            </div>

                            <div class="col-xs-12">
                                <p>Your care team will check in with you at {{$patient->getPhone()}} periodically.</p>
                            </div>
                        </div>
                    </div>

                    <!-- INSTRUCTIONS -->
                    <div class="patient-info__subareas pb-before">
                        <div class="row">
                            <div class="col-xs-12 print-only">
                                <h1 class="patient-summary__title patient-summary__title_9 patient-summary--careplan">
                                    Care
                                    Plan
                                    Part 2</h1>
                            </div>

                            <div class="col-xs-12">
                                <h2 class="patient-summary__subtitles patient-summary--careplan-background">Follow these
                                    Instructions:</h2>
                            </div>
                            <div class="col-xs-12">
                                <p></p>
                            </div>
                            @foreach ($problemsWithInstructions as $problem)
                                <div class="col-xs-12">
                                    <h3 class="patient-summary__subtitles--subareas patient-summary--careplan">
                                        For {{$problem['name']}}:</h3>
                                    @foreach (explode("\n", $problem['instruction']['name']) as $instruction)
                                        <p>{!! $instruction == '' ? "<br>" : $instruction !!}</p>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- OTHER INFORMATION -->
                    <div class="row pb-before">
                        <div class="col-xs-12 print-only">
                            <h1 class="patient-summary__title patient-summary__title_9  patient-summary--careplan">Care
                                Plan
                                Part 3</h1>
                        </div>
                        <div class="col-xs-12">
                            <h1 class="patient-summary__title--secondary patient-summary--careplan"><p>Other
                                    information</p>
                            </h1>
                        </div>
                    </div>

                    <!-- ALLERGIES -->
                    <div class="patient-info__subareas">
                        <div class="row">
                            <div class="col-xs-12">
                                <h2 class="patient-summary__subtitles patient-summary--careplan-background">
                                    Allergies:</h2>
                            </div>
                            <div class="col-xs-12">
                                @if($careplan['allergies'])
                                    <p><?= nl2br($careplan['allergies']); ?></p>
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
                                    <p><?= nl2br($careplan['social']); ?></p>
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
                                <h2 class="patient-summary__subtitles patient-summary--careplan-background">Care
                                    Team:</h2>
                            </div>
                            <div class="col-xs-12">
                                <ul class="col-xs-12">
                                    @foreach($careTeam as $carePerson)
                                        <li class="col-xs-12">
                                            <div class="col-md-7">
                                                <p style="margin-left: -10px;">
                                                    <strong>
                                                        {{snakeToSentenceCase($carePerson->type)}}:
                                                    </strong>{{optional($carePerson->user)->getFirstName()}} {{optional($carePerson->user)->getLastName()}} {{ optional($carePerson->user)->getSuffix() }}
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
                                <?php $careplan['other']; ?>

                                @if($careplan['other'])
                                    <p><?= nl2br($careplan['other']); ?></p>
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