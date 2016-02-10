@extends('partials.providerUI')

<?php
function biometricGoal($starting, $target){
    $starting = explode('/', $starting);
    $starting = $starting[0];
    $target = explode('/', $target);
    $target = $target[0];
    return ($starting > $target) ? 'Lower' :  'Raise';
}
$billing = App\User::find($patient->getBillingProviderIDAttribute());
$lead = App\User::find($patient->getLeadContactIDAttribute());

$today = \Carbon\Carbon::now()->toFormattedDateString();
$provider = App\User::find($patient->getLeadContactIDAttribute());
?>

@section('title', 'Care Plan View/Print')
@section('activity', 'Care Plan View/Print')

@section('content')
    <div class="container">
        <section class="patient-summary">
            <div class="patient-info__main">
                <div class="row">
                    <div class="col-xs-12 text-right hidden-print">
					<span class="btn btn-group text-right">
						<a style="margin-right:10px;" class="btn btn-info btn-sm inline-block" aria-label="..."
                           role="button" href="{{ URL::route('patients.listing') }}">Approve More Care Plans</a>
					<a class="btn btn-info btn-sm inline-block" aria-label="..." role="button"
                       HREF="javascript:window.print()">Print This Page</a>
				<form class="lang" action="#" method="POST" id="form">
                    <input type="hidden" name="lang" value="es"/>
                    <!-- <button type="submit" class="btn btn-info btn-sm text-right" aria-label="..." value="">Translate to Spanish</button>
          -->
                </form></span></div>
                </div>
                <div class="row gutter">
                    <div class="col-xs-12">
                        <h1 class="patient-summary__title patient-summary__title_9 patient-summary--careplan">Care
                            Plan</h1>
                    </div>
                </div>
                <div class="row gutter">
                    <div class="col-xs-12 col-md-4 print-row text-bold">{{$patient->fullName}}</div>
                    <div class="col-xs-12 col-md-4 print-row">{{$patient->phone}}</div>
                    <div class="col-xs-12 col-md-3 print-row">{{$today}}</div>
                </div>
                <div class="row gutter">
                    <div class="col-xs-12 col-md-4 print-row text-bold">
                        @if($provider)
                            {{$provider->fullName}}
                        @else
                            <em>no lead contact</em>
                        @endif
                    </div>
                    <div class="col-xs-12 col-md-4 print-row">
                        @if($provider)
                            {{$provider->phone}}
                        @endif
                    </div>
                    <div class="col-xs-12 col-md-4 print-row text-bold">{{$patient->getPreferredLocationName()}}</div>
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
                            @foreach($treating as $key => $value)
                                <li class='subareas__item inline-block col-xs-6 col-sm-3 print-row text-bold'>{{$key}}</li>
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
                            @foreach($biometrics as $key => $value)
                                <div class="col-xs-5 print-row text-bold">{{ biometricGoal($value['starting'], $value['target'])}} {{$key}}</div>
                                <div class="col-xs-4 print-row text-bold">to {{$value['target']}}</div>
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
                            @foreach($medications_monitor as $medi)
                                <li>{{$medi}}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="col-xs-10">
                        <ul><strong>Taking these Medications</strong>
                            <li>{{$taking_medications}}</li>
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
                            <li class='subareas__item inline-block  print-row'>{{implode(", ", $symptoms)}}
                            </li>
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
                            @foreach($lifestyle as $style)
                                <li class='subareas__item inline-block col-xs-6 col-sm-3 print-row text-bold'>{{$style}}</li>
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
            <?php foreach($treating as $key => $value){ ?>
            <!-- Hypertension -->
            <div class="patient-info__subareas">
                <div class="row">
                    <div class="col-xs-12">
                        <h3 class="patient-summary__subtitles--subareas patient-summary--careplan">For
                            <?= $key ?>:</h3>
                    </div>
                    <div class="col-xs-12">
                        <p><?= $value ?></p>
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
                        <p>{{$allergies}}</p>
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
                        <p>{{$social}}</p>
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
                            <strong>Billing Provider: </strong> {{$billing->getFullNameAttribute()}} <br>
                            <strong>Lead Contact: </strong>     {{$lead->getFullNameAttribute()}}
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
                        <p>{{$appointments}}</p>
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
                        <p>{{$other}}</p>
                    </div>
                </div>
            </div>
            <!-- /OTHER NOTES -->
            <!-- /OTHER INFORMATION -->
        </section>
    </div>
@stop