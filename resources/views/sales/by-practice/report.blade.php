<?php

$enrollmentSection = \App\Reports\Sales\Practice\Sections\EnrollmentSummary::class;
$rangeSection = \App\Reports\Sales\Practice\Sections\RangeSummary::class;
$financialSection = \App\Reports\Sales\Practice\Sections\FinancialSummary::class;
$practiceSection = \App\Reports\Sales\Practice\Sections\PracticeDemographics::class;
//dd($data[$enrollmentSection]);
?>


<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"
      integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous"
      xmlns="http://www.w3.org/1999/html">

<div class="page-header">
    <h1>{{$data['name']}}
        <small><br/>
            <b><span style="color: #50b2e2"> CircleLink Health </span>Sales Report - {{$data['start']}}
                to {{$data['end']}}</b></small>
    </h1>
</div>

{{--@if(array_key_exists($rangeSection, $data))--}}
{{--<div>--}}
{{--<h3>Overall Summary</h3>--}}

{{--<p>--}}
{{--Last week at your offices CircleLink nurses placed {{$data[$rangeSection]['no_of_call_attempts']}}--}}
{{--calls, including {{$data[$rangeSection]['no_of_successful_calls']}} successful phone sessions, totaling--}}
{{--{{$data[$rangeSection]['total_ccm_time']}} care hours. We also collected--}}
{{--{{$data[$rangeSection]['no_of_biometric_entries']}} vitals readings and our nurses forwarded--}}
{{--{{$data[$rangeSection]['no_of_forwarded_notes']}} notifications to you.--}}
{{--</p>--}}

{{--<p>--}}
{{--You can see a list of forwarded notes for your patients <a--}}
{{--href="{{$data[$rangeSection]['link_to_notes_listing']}}">here</a>,--}}
{{--including {{$data[$rangeSection]['no_of_forwarded_emergency_notes']}} notifications that your patient is in--}}
{{--the ER/Hospital.--}}
{{--</p>--}}

{{--</div>--}}

{{--@endif--}}

@if(array_key_exists($enrollmentSection, $data))

    <h3>Enrollment Summary</h3>

    <?php $currentMonth = Carbon\Carbon::now()->format('F Y') ?>
    <dl class="dl-horizontal">
        <h4>
            <dt>Current Cumulative:</dt>
            <dt>Enrolled <span style="color: green"> {{$data[$enrollmentSection]['enrolled'] ?? 'N/A'}} </span></dt>
            <dt>Withdrawn <span style="color: darkred"> {{$data[$enrollmentSection]['withdrawn'] ?? 'N/A'}} </span></dt>
            <dt>Paused<span style="color: darkorange"> {{$data[$enrollmentSection]['paused'] ?? 'N/A'}} </span></dt>

        </h4>
    </dl>

    <table class="table table-bordered">
        <tr>
            <td>Type</td>
            <th>{{\Carbon\Carbon::now()->format('F') . ' to Date'}}</th>
            <th>{{\Carbon\Carbon::now()->subMonths(1)->format('F')}}</th>
            <th>{{\Carbon\Carbon::now()->subMonths(2)->format('F')}}</th>
            <th>{{\Carbon\Carbon::now()->subMonths(3)->format('F')}}</th>

        </tr>
        @foreach($data[$enrollmentSection]['historical'] as $key => $value)
            <tr>
            @foreach($value as $i => $j)
                    <td>{{$j['added']}}</td>
            @endforeach
            </tr>

        @endforeach

    </table>

    {{--<table class="table table-bordered">--}}
    {{--<tr>--}}
    {{--<th>Type</th>--}}
    {{--<th>{{\Carbon\Carbon::now()->format('F') . ' to Date'}}</th>--}}
    {{--<th>{{\Carbon\Carbon::now()->subMonths(1)->format('F')}}</th>--}}
    {{--<th>{{\Carbon\Carbon::now()->subMonths(2)->format('F')}}</th>--}}
    {{--<th>{{\Carbon\Carbon::now()->subMonths(3)->format('F')}}</th>--}}
    {{--</tr>--}}

    {{--<tr>--}}

    {{--<td>withdrawn</td>--}}
    {{--<td>paused</td>--}}
    {{--<td>added</td>--}}
    {{--<td>billable</td>--}}

    {{--<tr>--}}

    {{--<td>{{$key}}</td>--}}
    {{--<td>{{$value['withdrawn'] ?? 'N/A'}}</td>--}}
    {{--<td>{{$value['paused'] ?? 'N/A'}}</td>--}}
    {{--<td>{{$value['added'] ?? 'N/A'}}</td>--}}
    {{--<td>{{$value['billable'] ?? 'N/A'}}</td>--}}

    {{--</tr>--}}
    {{--</table>--}}

@endif

{{--@if(array_key_exists($financialSection, $data))--}}

{{--<h3>Financial Performance</h3>--}}

{{--<?php $currentMonth = Carbon\Carbon::now()->format('F Y') ?>--}}
{{--<dl class="dl-horizontal">--}}
{{--<h4>--}}
{{--<dt>CCM Revenue to date: <span style="color: green"> {{$data['sections']['Financial Performance']['revenue_so_far'] ?? 'N/A'}} </span></dt>--}}
{{--<dt>CCM Profit to date: <span style="color: green"> {{$data['sections']['Financial Performance']['profit_so_far'] ?? 'N/A'}} </span></dt>--}}
{{--<dt>Patients billed to date:<span style="color: #50b2e2"> {{$data['sections']['Financial Performance']['billed_so_far'] ?? 'N/A'}} </span></dt>--}}

{{--</h4>--}}
{{--</dl>--}}


{{--<table class="table table-bordered">--}}
{{--<tr>--}}
{{--<th>Type</th>--}}
{{--<th>{{\Carbon\Carbon::now()->format('F') . ' to Date'}}</th>--}}
{{--<th>{{\Carbon\Carbon::now()->subMonths(1)->format('F')}}</th>--}}
{{--<th>{{\Carbon\Carbon::now()->subMonths(2)->format('F')}}</th>--}}
{{--<th>{{\Carbon\Carbon::now()->subMonths(3)->format('F')}}</th>--}}
{{--</tr>--}}

{{--@foreach($data['sections']['Enrollment Summary'] as $key => $value)--}}
{{--<tr>--}}

{{--<td>{{''}}</td>--}}
{{--<td>{{$value['withdrawn']}}</td>--}}
{{--<td>{{$value['paused']}}</td>--}}
{{--<td>{{$value['added']}}</td>--}}
{{--<td>{{$value['billable']}}</td>--}}

{{--</tr>--}}
{{--@endforeach--}}
{{--</table>--}}

{{--@endif--}}

{{--@if(array_key_exists($practiceSection, $data))--}}


{{--@endif--}}

