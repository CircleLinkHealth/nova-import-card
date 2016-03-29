@extends('partials.providerUI')

<?php
function biometricGoal($starting, $target, $bp = false)
{
    $starting = explode('/', $starting);
    $starting = $starting[0];
    $target = explode('/', $target);
    $target = $target[0];
    $verb = 'Raise';
    if ($bp == 'Blood Pressure') {
        $verb = 'Maintain';
    };
    if ($bp == 'Weight') {
        $verb = 'Maintain';
    };
    return ($starting > $target) ? 'Lower' : $verb;
}

        //check if exists
 function checkIfExists($arr,$val){
    if(isset($arr[$val])){
        return $arr[$val];
    }
     return '';
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
        $config = $patient->userConfig();
        $billing = App\User::find($patient->getBillingProviderIDAttribute());
        $lead = App\User::find($patient->getLeadContactIDAttribute());
       ?>
<style type="text/css">
    div.address { line-height: 1.1em; 
        font-family: 'Roboto', sans-serif;
    }
</style>
        <div class="container">
            <section class="patient-summary">
                <div class="patient-info__main">
                    <div class="row">
                        <div class="col-xs-12 text-right hidden-print">
					<span class="btn btn-group text-right">
					<A class="btn btn-info btn-sm inline-block" aria-label="..." role="button" HREF="javascript:window.print()">Print This Page</A>
				<form class="lang" action="#" method="POST" id="form">
                    <input type="hidden" name="lang" value="es" />
                    <!-- <button type="submit" class="btn btn-info btn-sm text-right" aria-label="..." value="">Translate to Spanish</button>
          -->
                </form></span></div>
                    </div>
                    <div class="patient-info__main">
                    </div>
                    <div class="patient-info__main">
                        <div class="row gutter">
                            <div class="col-xs-12" style="background-image: url(http://testcrisfield.careplanmanager.com/wp-content/themes/CLH_Provider/templates/images/clh_logo_sm.png); height: 70px; background-repeat: no-repeat;background-position: 50%;">
                                <div class="col-xs-1 col-xs-offset-5"><!-- img src="http://testcrisfield.careplanmanager.com/wp-content/themes/CLH_Provider/templates/images/clh_logo.png" --></div>
                                <div class="col-xs-7 address"><strong>On Behalf of</strong></div>
                                <div class="col-xs-4 col-xs-offset-1 print-row text-right">290 Harbor Drive</div>
                                <div class="col-xs-7 address">{{$patient->getPreferredLocationAddress()->address_line_1}}</div>
                                <div class="col-xs-4 col-xs-offset-1 print-row text-right">Stamford, CT 06902</div>
                                <div class="col-xs-7 address">{{$patient->getPreferredLocationName()}}</div>
                                <div class="col-xs-4 col-xs-offset-1 print-row text-right">Phone: 203 847 5890</div>
                                <div class="col-xs-7 address">{{$patient->getPreferredLocationAddress()->city}}, {{$patient->getPreferredLocationAddress()->state}} {{$patient->getPreferredLocationAddress()->postal_code}}</div>
                                <div class="col-xs-4 col-xs-offset-1 print-row text-right">Fax: 203 847 5899</div>
                                <!-- <div class="col-xs-12 address"></div> -->
                            </div>
                        </div>

                        <div class="row gutter">
                        </div>
                        <div class="row gutter">
                            <div class="col-xs-12 col-sm-12">&nbsp;</div>
                        </div>
                        <div class="row gutter">
                            <div class="col-xs-12 col-sm-12">&nbsp;</div>
                        </div>
                        <div class="row gutter">
                            <div class="col-xs-12 col-sm-12">&nbsp;</div>
                        </div>
                        <div class="row gutter">
                            <div class="col-xs-12 col-sm-12">&nbsp;</div>
                        </div>
                        <div class="row gutter">
                            <div class="col-xs-12">
                                <div class="col-xs-9 address">{{strtoupper($patient->fullName)}}</div>
                                <div class="col-xs-9 address">{{strtoupper($patient->address)}}</div>
                                <div class="col-xs-9 address"> {{strtoupper($patient->city)}}, {{strtoupper($patient->state)}} {{strtoupper($patient->zip)}}</div>
                            </div>
                        </div>
                        <div class="row address">
                        </div>
                        <div class="row address">
                        </div>
                        <div class="row gutter">
                        </div>
                        <div class="row gutter">
                            <div class="col-xs-10 text-right"><?= date("F d, Y") ?></div>
                        </div>
                        <div class="row gutter">
                            <div class="col-xs-10 welcome-copy">
                                <div class="row gutter">
                                    <BR><BR><BR>
                                    Dear {{ucfirst(strtolower($patient->first_name))}} {{ucfirst(strtolower($patient->last_name))}},</div>
                                <div class="row gutter">
                                </div>
                                <div class="row gutter" style="line-height: 1.0em;">
                                    Welcome to your doctors's chronic care management program! We are happy that you have decided to enroll in this very worthwhile program designed for Medicare patients like you. As a participant, you will benefit in a number of ways:
                                </div>
                                <div class="row gutter"><BR>
                                    <ul type="disc" style="line-height: 1.0em;list-style-type: disc;">
                                        <li style="list-style-type: disc;margin: 0 0;">Have 24/7 access to your care team by calling (844) 968-1800</li>
                                        <li style="list-style-type: disc;margin: 15px 0;">Receive a weekly call to check up on how you are doing</li>
                                        <li style="list-style-type: disc;margin: 15px 0;">Avoid the inconvenience of frequent office visits and co-pays by using this program's remote care</li>
                                        <li style="list-style-type: disc;margin: 15px 0;">All of the information gathered will be available to your doctor and will allow them to see how you are doing even when you are not in their office</li>
                                        <li style="list-style-type: disc;margin: 5px 0;">This program will help you take better care of yourself by staying connected to your care team and doctor</li>
                                    </ul>
                                </div>
                                <div class="row gutter" style="line-height: 1.0em;">
                                    Enclosed please find a copy of your personalized care plan. Please take a few minutes to review the care plan and call us if you have any questions.
                                </div>
                                <div class="row gutter"><BR>
                                </div>
                                <div class="row gutter">
                                    Don't forget your care team can be reached 24/7 at the following number:
                                </div>
                                <div class="row gutter">
                                </div>
                                <div class="row gutter text-bold text-center">
                                    (844) 968-1800
                                </div>
                                <div class="row gutter">
                                </div>
                                <div class="row gutter">
                                    Thanks and we look forward to working with you!
                                </div>
                                <div class="row gutter">
                                </div>
                                <div class="row gutter">
                                    Best,
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

                    <!-- <div class="row pb-before" style="color:white;">This page left intentionally blank</div> -->

                    <div class="row pb-before"></div>
                    <div class="row gutter">
                        <div class="col-xs-12">
                            <h1 class="patient-summary__title patient-summary__title_9 patient-summary--careplan">Care
                                Plan</h1>
                        </div>
                    </div>
                    <div class="row gutter">
                        <div class="col-xs-12 col-md-4 print-row text-bold">{{$patient->fullName}}</div>
                        <div class="col-xs-12 col-md-3 print-row">{{$patient->phone}}</div>
                        <div class="col-xs-12 col-md-5 print-row text-right">{{$today}}</div>
                    </div>
                    <div class="row gutter">
                        <div class="col-xs-12 col-md-4 print-row text-bold">
                            @if($billing)
                                {{$billing->fullName}} {{($billing->getSpecialtyAttribute() == '')? '' :  $billing->getSpecialtyAttribute() }}
                            @else
                                <em>No Billing Provider Selected</em>
                            @endif
                        </div>
                        <div class="col-xs-12 col-md-3 print-row">
                            @if($billing)
                                {{$billing->phone}}
                            @endif
                        </div>
                        <div class="col-xs-12 col-md-5 print-row text-bold text-right">{{$patient->getPreferredLocationName()}}</div>
                    </div>
                </div>
                <!-- CARE AREAS -->
                <div class="patient-info__subareas">
                    <div class="row">
                        <div class="col-xs-12">
                            <h2 class="patient-summary__subtitles patient-summary--careplan-background">We Are Treating</h2>
                        </div>
                    </div>
                    <div class="row gutter">
                        <div class="col-xs-12">
                            <ul class="subareas__list">
                                @foreach($careplan['treating'] as $key => $value)
                                    <li class='subareas__item inline-block col-xs-6 col-sm-3 print-row'>{{$key}}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- /CARE AREAS -->
                <!-- BIOMETRICS -->
                <div class="patient-info__subareas">
                    <div class="row">
                        <div class="col-xs-12">
                            <h2 class="patient-summary__subtitles patient-summary--careplan-background">Your Health
                                Goals</h2>
                        </div>
                    </div>
                    <div class="row">
                        <ul class="subareas__list">
                            <li class="subareas__item subareas__item--wide col-sm-12">
                                @foreach(array_reverse($careplan['bio_data']) as $key => $value)
                                    <div class="col-xs-5 print-row text-bold">{{ biometricGoal($value['starting'], $value['target'], $key)}} {{$key}}</div>
                                    <div class="col-xs-4 print-row text-bold">{{(biometricGoal($value['starting'], $value['target'], $key) == 'Maintain')? 'at' :  'to' }} {{$value['target']}}</div>
                                    <div class="col-xs-3 print-row">from {{$value['starting']}}</div>
                                @endforeach
                            </li>
                        </ul>
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
                                @foreach($careplan['medications'] as $medi)
                                    <li>{{$medi}}</li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="col-xs-10">
                            <ul><strong>Taking these Medications</strong>
                                <li>{{$careplan['taking_meds']}}</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- /MEDICATIONS -->

                <!-- SYMPTOMS -->
                <div class="patient-info__subareas">
                    <div class="row">
                        <div class="col-xs-12">
                            <h2 class="patient-summary__subtitles patient-summary--careplan-background">Watch out for:</h2>
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
                            <h2 class="patient-summary__subtitles patient-summary--careplan-background">We Are Informing You
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
                            <h1 class="patient-summary__title patient-summary__title_9 patient-summary--careplan">Care Plan
                                Part 2</h1>
                        </div>

                        <div class="col-xs-12">
                            <h2 class="patient-summary__subtitles patient-summary--careplan-background">Check In Plan</h2>
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
                <?php foreach($careplan['treating'] as $key => $value){ ?>
                        <!-- Hypertension -->
                <div class="patient-info__subareas">
                    <div class="row">
                        <div class="col-xs-12">
                            <h3 class="patient-summary__subtitles--subareas patient-summary--careplan">For
                                <?= $key ?>:</h3>
                        </div>
                        <div class="col-xs-12">
                            <p><?= nl2br($value) ?></p>
                        </div>
                    </div>
                </div>
                <?php } ?>

                        <!-- /INSTRUCTIONS -->

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
                            <p>
                                @if($billing)
                                    <strong>Billing
                                        Provider: </strong> {{$billing->fullName}} {{($billing->getSpecialtyAttribute() == '')? '' : ' ' .  $billing->getSpecialtyAttribute() }}
                                    <br>
                                @endif
                                @if($lead)
                                    <strong>Lead
                                        Contact: </strong>     {{$lead->getFullNameAttribute()}}{{($lead->getSpecialtyAttribute() == '')? '' : ' ' .  $lead->getSpecialtyAttribute() }}
                                    <br>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
                <!-- /CARE TEAM -->


                <!-- Appointments -->
                <div class="patient-info__subareas">
                    <div class="row">
                        <div class="col-xs-12">
                            <h2 class="patient-summary__subtitles patient-summary--careplan-background">Appointments:</h2>
                        </div>
                        <div class="col-xs-12">
                            @if($careplan['appointments'])
                                <p><?= nl2br($careplan['appointments']) ?></p>
                            @else
                                <p>No instructions at this time</p>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- /Appointments -->

                <!-- OTHER NOTES -->
                <div class="patient-info__subareas">
                    <div class="row">
                        <div class="col-xs-12">
                            <h2 class="patient-summary__subtitles patient-summary--careplan-background">Other Notes:</h2>
                        </div>
                        <div class="col-xs-12">
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
    @endforeach
@stop