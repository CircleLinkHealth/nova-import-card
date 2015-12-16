@extends('partials.providerUI')

@section('content')
    <div class="container">
        <section class="patient-summary">
<div class="row" style="margin-top:60px;">
    <div class="patient-info__main">
        <div class="row">
            <div class="col-xs-12 text-right hidden-print">
					<span class="btn btn-group text-right">
					<A class="btn btn-info btn-sm inline-block" aria-label="..." role="button" HREF="javascript:window.print()">Print This Page</A>
				</span></div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <h1 class="patient-summary__title patient-summary__title_7 patient-summary--careplan patient-summary--progress-report">Progress Report:</h1>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-md-4 print-row text-bold">!!Issac Newton</div>
            <div class="col-xs-12 col-md-4 print-row">999-999-9999</div>
            <div class="col-xs-12 col-md-3 print-row">12/16/2015</div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-md-4 print-row text-bold"> Linda Warshavsky </div>
            <div class="col-xs-12 col-md-4 print-row">203-252-2556</div>
            <div class="col-xs-12 col-md-4 print-row text-bold">Crisfield Clinic South</div>
        </div>
    </div>
    </div>
            <div class="patient-info__subareas">
                <div class="row">
                    <div class="col-xs-12">
                        <h2 class="patient-summary__subtitles patient-summary--progress-report-background">We Are Treating</h2>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <ul class="subareas__list">
                            @foreach($treating as $treat)
                                <li class='subareas__item inline-block col-xs-6 col-sm-3 print-row text-bold'>{{$treat}}</li>
                            @endforeach
    				</ul>
                    </div>
                </div>
            </div>
            <!-- /CARE AREAS -->
            <!-- TRACKING CHANGES -->
            <div class="patient-info__subareas">
                <div class="row">
                    <div class="col-xs-12">
                        <h2 class="patient-summary__subtitles patient-summary--progress-report-background">Tracking Changes</h2>
                    </div>
                </div>
            </div>
            <!-- /TRACKING CHANGES -->


            <!-- MEDICATIONS -->
            <div class="patient-info__subareas ">
                <div class="row">
                    <div class="col-xs-12">
                        <h2 class="patient-summary__subtitles patient-summary--progress-report-background">Taking Your Medications?</h2>
                    </div>
                </div>
                <div class="row medication-rating">
                    <div class="col-xs-12 col-sm-4">
                        <i class="icon--face icon--face--good">
                        </i>
                        <ul>
                            <li class='text-bold medication-rating__title'>Good</li>
                            @foreach($medications as $section)
                                @if($section['Section'] == 'Better')
                            <li>{{$section['name']}}</li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                    <div class="col-xs-12 col-sm-4">
                        <i class="icon--face icon--face--work">
                        </i>
                        <ul>
                            <li class='text-bold medication-rating__title'>Needs Work</li>
                            @foreach($medications as $section)
                                @if($section['Section'] == 'Needs Work')
                                    <li>{{$section['name']}}</li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                    <div class="col-xs-12 col-sm-4">
                        <i class="icon--face icon--face--bad">
                        </i>
                        <ul>
                            <li class='text-bold medication-rating__title'>Bad</li>
                            @foreach($medications as $section)
                                @if($section['Section'] == 'Worse')
                                    <li>{{$section['name']}}</li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            <!-- /MEDICATIONS -->
        </section>
    </div>
</div>
@stop