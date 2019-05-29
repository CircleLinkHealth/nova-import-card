@isset($isPdf)
    <link href="{{asset('/css/bootstrap.min.css')}}" rel="stylesheet">
@endisset

<style>
    .pdf-line {
        display: block;
        padding-bottom: 5px;
        padding-top: 5px;
    }

    .cross-out {
        text-decoration: line-through;
    }

    .display-inline-block {
        display: inline-block;
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
        <div class="pdf-line">
            <b>Base Salary @if($changedToFixedRateBecauseItYieldedMore)
                    <small>(highest amount used)</small>@endif:</b>
            @if(is_array($formattedBaseSalary))
                <span class="display-inline-block">{{$formattedBaseSalary['high']}}</span>
                <span class="display-inline-block cross-out"><small>{{$formattedBaseSalary['low']}}</small></span>
            @else
                <span class="display-inline-block">formattedBaseSalary}}</span>
            @endif
        </div>

        @if($hasAddedTime)
            <div class="pdf-line"><b>Extras:</b> <span>${{$addedTimeAmount}} ({{$addedTime}}
                    Hours at {{$nurseHourlyRate}}/hr)@if($bonus), Cash Bonuses: ${{$bonus}} @endif</span></div>
        @endif

        <div class="pdf-line"><b><u>Invoice Total</u>:</b> <span><b>{{$formattedInvoiceTotalAmount}}</b></span></div>
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