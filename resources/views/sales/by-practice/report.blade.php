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
<div class="container">
<div class="page-header">
    <h1>{{$data['name']}}
        <small><br/>
            <b><span style="color: #50b2e2"> CircleLink Health </span>Sales Report - {{$data['end']}}</b></small>
    </h1>
</div>

    @if(array_key_exists($rangeSection, $data))
        <div>
            <h3>Overall Summary</h3>

            <p>
                Last week at your offices CircleLink nurses placed {{$data[$rangeSection]['no_of_call_attempts']}}
                calls, including {{$data[$rangeSection]['no_of_successful_calls']}} successful phone sessions, totaling
                {{$data[$rangeSection]['total_ccm_time']}} care hours. We also collected
                {{$data[$rangeSection]['no_of_biometric_entries']}} vitals readings and our nurses forwarded
                {{$data[$rangeSection]['no_of_forwarded_notes']}} notifications to you.
            </p>

            <p>
                You can see a list of forwarded notes for your patients <a
                        href="{{$data[$rangeSection]['link_to_notes_listing']}}">here</a>,
                including {{$data[$rangeSection]['no_of_forwarded_emergency_notes']}} notifications that your patient is
                in
                the ER/Hospital.
            </p>

        </div>

    @endif

    <hr/>


@if(array_key_exists($enrollmentSection, $data))

    <h3>Enrollment Summary</h3>

    <?php $currentMonth = Carbon\Carbon::now()->format('F Y') ?>
    <div class="">
        <h5>
            <ul>
                Current Cumulative:<br/>
                <li>Enrolled <span style="color: green"> {{$data[$enrollmentSection]['enrolled'] ?? 'N/A'}} </span></li>
                <li>Withdrawn <span style="color: darkred"> {{$data[$enrollmentSection]['withdrawn'] ?? 'N/A'}} </span>
                </li>
                <li>Paused<span style="color: darkorange"> {{$data[$enrollmentSection]['paused'] ?? 'N/A'}} </span></li>
            </ul>
        </h5>

        <table class="table table-bordered">
            <tr>
                <td>Type</td>
                <th>{{\Carbon\Carbon::now()->format('F') . ' to Date'}}</th>
                <th>{{\Carbon\Carbon::now()->subMonths(1)->format('F')}}</th>
                <th>{{\Carbon\Carbon::now()->subMonths(2)->format('F')}}</th>
                <th>{{\Carbon\Carbon::now()->subMonths(3)->format('F')}}</th>
                <th>{{\Carbon\Carbon::now()->subMonths(4)->format('F')}}</th>

            </tr>

            @foreach($data[$enrollmentSection]['historical'] as $key => $values)
                <tr>
                    <td>{{$key}}</td>
                    @foreach($values as $value)
                        <td>{{$value}}</td>
                    @endforeach
                </tr>
            @endforeach
        </table>
    </div>

@endif

    <hr/>


@if(array_key_exists($financialSection, $data))

    <h3>Financial Performance</h3>

    <?php $currentMonth = Carbon\Carbon::now()->format('F Y') ?>
    <ul class="">
        <h5>
            <li>CCM Revenue to date: <span
                        style="color: green"> {{$data[$financialSection]['revenue_so_far'] ?? 'N/A'}} </span>
            </li>
            <li>CCM Profit to date: <span
                        style="color: green"> {{$data[$financialSection]['profit_so_far'] ?? 'N/A'}} </span>
            </li>
            <li>Patients billed to date:<span
                        style="color: #50b2e2"> {{$data[$financialSection]['billed_so_far'] ?? 'N/A'}} </span>
            </li>

        </h5>
    </ul>


        <table class="table table-bordered">
            <tr>
                <td>Type</td>
                <th>{{\Carbon\Carbon::now()->subMonths(1)->format('F')}}</th>
                <th>{{\Carbon\Carbon::now()->subMonths(2)->format('F')}}</th>
                <th>{{\Carbon\Carbon::now()->subMonths(3)->format('F')}}</th>
                <th>{{\Carbon\Carbon::now()->subMonths(4)->format('F')}}</th>

            </tr>

            @foreach($data[$financialSection]['historical'] as $key => $values)
                <tr>
                    <td>{{$key}}</td>
                    @foreach($values as $value)
                        <td>{{$value}}</td>
                    @endforeach
                </tr>
            @endforeach
        </table>

@endif

    <hr/>


@if(array_key_exists($practiceSection, $data))

        <h3>Practice Demographics</h3>

        <p>Your team has {{$data[$practiceSection]['lead']}} lead(s): N/A. In total, there
        are {{$data[$practiceSection]['total']}} members on your CCM team (that’s not
        including {{$data[$practiceSection]['disabled']}} disabled users).
    </p>
    <p>Of the active users, {{$data[$practiceSection]['providers']}} are Providers, {{$data[$practiceSection]['cc']}}
        are
        RNs, {{$data[$practiceSection]['oas']}} are office staff and {{$data[$practiceSection]['mas']}} are MAs.
    </p>
@endif
    <hr/>

</div>
