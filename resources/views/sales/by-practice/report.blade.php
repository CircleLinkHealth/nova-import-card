<?php

    $enrollmentSection = \App\Reports\Sales\Practice\Sections\EnrollmentSummary::class;
    $rangeSection = \App\Reports\Sales\Practice\Sections\RangeSummary::class;
    $financialSection = \App\Reports\Sales\Practice\Sections\FinancialSummary::class;
    $practiceSection = \App\Reports\Sales\Practice\Sections\PracticeDemographics::class;

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

        @include('sales.partials.overall-section', ['data' => $data])

    @endif

    @if(array_key_exists($enrollmentSection, $data))

        <hr/>

        <h3>Enrollment Summary</h3>

        <?php $currentMonth = Carbon\Carbon::now()->format('F Y') ?>
        <div class="">
            <h5>
                <ul>
                    Current Cumulative:<br/>
                    <li>Enrolled <span style="color: green"> {{$data[$enrollmentSection]['enrolled'] ?? 'N/A'}} </span>
                    </li>
                    <li>Withdrawn <span
                                style="color: darkred"> {{$data[$enrollmentSection]['withdrawn'] ?? 'N/A'}} </span>
                    </li>
                    <li>Paused<span style="color: darkorange"> {{$data[$enrollmentSection]['paused'] ?? 'N/A'}} </span>
                    </li>
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

    @if(array_key_exists($financialSection, $data))

        <hr/>

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

    @if(array_key_exists($practiceSection, $data))

        <hr/>

        @include('sales.partials.practice-section', ['data' => $data])

    @endif

    <hr/>

</div>
