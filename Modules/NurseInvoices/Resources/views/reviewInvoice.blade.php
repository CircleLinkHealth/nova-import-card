<link href="{{asset('/css/bootstrap.min.css')}}" rel="stylesheet">

<style>
    .pdf-line {
        display: block;
        padding-bottom: 5px;
    }
</style>

<div class="container">
    <div class="page-header">
        <h1>CircleLink Health
            <small>
                Time Report for <b>{{$invoiceData['nurseFullName']}}</b> from {{$invoiceData['startDate']}} to {{$invoiceData['endDate']}}
            </small>
        </h1>
    </div>


    <dl class="dl-horizontal">
        <h4>
            @if($invoiceData['hasAddedTime'])
                <div class="pdf-line"><b>Extras:</b> <span>{{$invoiceData['addedTime']}} Hours (${{$invoiceData['addedTimeAmount']}})@if($invoiceData['bonus']), Cash Bonuses: ${{$invoiceData['bonus']}} @endif</span></div>
            @endif

            <dt>Invoice Amount
                <small>(highest amount used)</small>
            </dt>

            <dd>
                @if(is_array($invoiceData['totalBillableRate']))
                    <div style="display: block;">{{$invoiceData['totalBillableRate']['high']}}</div>
                    <div style="display: block; text-decoration: line-through;">
                        <small>{{$invoiceData['totalBillableRate']['low']}}</small>
                    </div>
                @else
                    {{$invoiceData['totalBillableRate']}}
                @endif
            </dd>
        </h4>
    </dl>

    <table class="table table-bordered">
        <tr>
            <th style="width: 25%">Date</th>
            @if(!$invoiceData['variablePay'])
                <th style="width: 25%">Minutes</th>
            @endif

            <th style="width: 25%">Total Hours</th>
            @if($invoiceData['variablePay'])
                <th style="width: 25%">CCM Hours (${{$invoiceData['nurseHighRate']}}/Hour)</th>
                <th style="width: 25%">CCM Hours (${{$invoiceData['nurseLowRate']}}/Hour)</th>
            @endif
        </tr>

        <tr>
            <td><b>Total Hours</b></td>

            @if(!$invoiceData['variablePay'])
                <td>{{$invoiceData['systemTimeInMinutes']}}</td>
            @endif

            <td>{{$invoiceData['systemTimeInHours']}}</td>


            @if($invoiceData['variablePay'])
                <td>{{$invoiceData['totalTimeTowardsCcm']}}</td>
                <td>{{$invoiceData['totalTimeAfterCcm']}}</td>
            @endif
        </tr>

        @foreach($invoiceData['timePerDay'] as $date => $row)
            <tr>
                <td><b>{{$date}}</b></td>

                @if(!$invoiceData['variablePay'])
                    <td>{{$row['minutes']}}</td>
                @endif

                <td>{{$row['hours']}}</td>

                @if($invoiceData['variablePay'])
                    <td>{{$row['towards']}}</td>
                    <td>{{$row['after']}}</td>
                @endif
            </tr>
        @endforeach
    </table>
</div>