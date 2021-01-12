@extends('sales.partials.report-layout')

<?php

$enrollmentSection = \App\Reports\Sales\Provider\Sections\EnrollmentSummary::class;
$rangeSection      = \App\Reports\Sales\Provider\Sections\RangeSummary::class;
$financialSection  = \App\Reports\Sales\Provider\Sections\FinancialSummary::class;
$practiceSection   = \App\Reports\Sales\Provider\Sections\PracticeDemographics::class;

?>

<style type="text/css">
    .myTable {
        background-color: #fff;
        border-collapse: collapse;
    }

    .myTable th {
        background-color: #fff;
        color: black;
        width: 50%;
    }

    .myTable td, .myTable th {
        padding: 5px;
        border: 1px solid black;
    }
</style>

@section('content')
    <div class="page-header">

        @if($data['isEmail'])
            <div style="text-align: center"><img src="{{mix('/img/ui/logo.png')}}"
                                                 alt="Care Plan Manager"
                                                 style="position:relative;"
                                                 width="200px"/>
                <h1 style="margin-bottom: 0px">CircleLink Weekly CCM Summary</h1>
                <b><span style="font-size: 16px">(Your Patients)</span></b><br/>

                <b><br><span>{{Carbon\Carbon::parse($data['start'])->format('l, jS F') . ' - ' . Carbon\Carbon::parse($data['end'])->format('l, jS F') }}</span></b>

            </div>
        @else
            <h1>{{$data['name']}}
                <small>CircleLink Health CCM Summary <b></b>
                    <span>({{Carbon\Carbon::parse($data['start'])->format('jS F') . ' - ' . Carbon\Carbon::parse($data['end'])->format('jS F') }}
                        )</span></small>
            </h1>
        @endif


    </div>

    @if(array_key_exists($rangeSection, $data))

        <div style="font-size: 16px">

            @if(!$data['isEmail'])
                <p>Here's a summary of CCM activities for the period
                    specified: {{Carbon\Carbon::parse($data['start'])->format('l, jS F') . ' - ' . Carbon\Carbon::parse($data['end'])->format('l, jS F') }}</p>
            @else
                <p>Hope you had a good weekend! Here's a summary of CCM activities for last week:</p>
            @endif


            <p>@if(!$data['isEmail']) During the period, @else Last week @endif CircleLink nurses placed
                <b>{{$data[$rangeSection]['no_of_call_attempts']}}</b>
                calls to your patients, including <b>{{$data[$rangeSection]['no_of_successful_calls']}}</b>
                successful phone sessions, totaling <b>{{$data[$rangeSection]['total_ccm_time']}}</b>
                care hours. We also collected <b>{{$data[$rangeSection]['no_of_biometric_entries']}}</b>
                vital and adherence reading(s), and our nurses forwarded
                <b>{{$data[$rangeSection]['no_of_forwarded_notes']}}</b>
                note(s) to you.</p>

            @if($data['isEmail'] && should_show_notes_report($data['practice_id']))
                <p>You can see a list of forwarded notes for your patients <a
                            href="{{$data[$rangeSection]['link_to_notes_listing']}}">here</a>,
                    including <b>{{$data[$rangeSection]['no_of_forwarded_emergency_notes']}}</b>
                    notification(s) indicating a patient visited a ER/Hospital.</p>
            @endif

        </div>
    @endif

    @if(array_key_exists($enrollmentSection, $data))

        @include('sales.partials.enrollement-section', ['data' => $data])

    @endif

    @if(array_key_exists($financialSection, $data))

        @include('sales.partials.financial-section', ['data' => $data])

    @endif

    @if(array_key_exists($practiceSection, $data))

        @include('sales.partials.practice-section', ['data' => $data])

    @endif

@stop
