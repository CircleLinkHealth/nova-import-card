<link href="{{asset('/css/bootstrap.min.css')}}" rel="stylesheet">

<style>
    .pdf-line {
        display: block;
        padding-bottom: 5px;
    }
</style>

<div class="page-header">
    <h1>CircleLink Health
        <small>
            Time Report for <b>{{$nurseFullName}}</b> from {{$startDate}} to {{$endDate}}
        </small>
    </h1>
</div>


<dl class="dl-horizontal">
    <h4>
        @if($hasAddedTime)
            <div class="pdf-line"><b>Extras:</b> <span>{{$addedTime}} Hours (${{$addedTimeAmount}})@if($bonus), Cash Bonuses: ${{$bonus}} @endif</span></div>
        @endif

        <dt>Invoice Amount
            <small>(highest amount used)</small>
        </dt>
        <dd>
            @if(is_array($totalBillableRate))
                <div style="display: block;">{{$totalBillableRate['high']}}</div>
                <div style="display: block; text-decoration: line-through;">
                    <small>{{$totalBillableRate['low']}}</small>
                </div>
            @else
                {{$totalBillableRate}}
            @endif
        </dd>
    </h4>
</dl>

<table class="table table-bordered">
    <tr>
        <th style="width: 25%">Date</th>
        @if(!$variablePay)
            <th style="width: 25%">Minutes</th>
        @endif

        <th style="width: 25%">Total Hours</th>
        @if($variablePay)
            <th style="width: 25%">CCM Hours (${{$nurseHighRate}}/Hour)</th>
            <th style="width: 25%">CCM Hours (${{$nurseLowRate}}/Hour)</th>
        @endif
    </tr>

    <tr>
        <td><b>Total Hours</b></td>

        @if(!$variablePay)
            <td>{{$systemTimeInMinutes}}</td>
        @endif

        <td>{{$systemTimeInHours}}</td>


        @if($variablePay)
            <td>{{$totalTimeTowardsCcm}}</td>
            <td>{{$totalTimeAfterCcm}}</td>
        @endif
    </tr>

    @foreach($timePerDay as $date => $row)
        <tr>
            <td><b>{{$date}}</b></td>

            @if(!$variablePay)
                <td>{{$row['minutes']}}</td>
            @endif

            <td>{{$row['hours']}}</td>

            @if($variablePay)
                <td>{{$row['towards']}}</td>
                <td>{{$row['after']}}</td>
            @endif
        </tr>
    @endforeach
</table>