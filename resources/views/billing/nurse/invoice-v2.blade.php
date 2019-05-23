<link href="{{asset('/css/bootstrap.min.css')}}" rel="stylesheet">

<div class="page-header">
    <h1>CircleLink Health
        <small>Monthly Time Report for <b>{{$user->getFullName()}}</b>
        </small>
    </h1>
</div>


<dl class="dl-horizontal">
    <h4>
        <dt>Duration</dt>
        <dd>{{$startDate}} to {{$endDate}}</dd>

        @if($hasAddedTime)

            <dt>Extras:</dt>
            <dd>{{$addedTime}} Hours (${{$addedTimeAmount}})</dd>

        @endif

        @if($bonus)
            <dt>Bonus:</dt>
            <dd>${{$bonus}}</dd>
        @endif

        <dt>Invoice Amount <small>(highest amount used)</small></dt>
        <dd>
            @if(is_array($totalBillableRate))
                <div style="display: block;">{{$totalBillableRate['high']}}</div>
                <div style="display: block; text-decoration: line-through;"><small>{{$totalBillableRate['low']}}</small></div>
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
            <th style="width: 25%">CCM Hours (${{$user->nurseInfo->high_rate}}/Hour)</th>
            <th style="width: 25%">CCM Hours (${{$user->nurseInfo->low_rate}}/Hour)</th>
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

    @foreach($invoiceTable as $date => $row)
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