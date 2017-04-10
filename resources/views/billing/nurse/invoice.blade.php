<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"
      integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

<div class="page-header">
    <h1>CircleLink Health
        <small>Monthly Time Report for <b>{{$nurse_name}}</b> ({{Carbon\Carbon::now()->toDateTimeString()}})</small>
    </h1>
</div>


<dl class="dl-horizontal">
    <h4>
        <dt>Duration</dt>
        <dd>{{$date_start}} to {{$date_end}}</dd>

        @if($hasAddedTime)

        <dt>Extras:</dt>
        <dd>{{$manual_time_notes}}: {{$manual_time. ' Hours'}} (${{$manual_time_amount}})</dd>

        @endif

        <dt>Invoice Amount</dt>
        <dd>{{$total_billable_amount}} ({{$total_billable_rate}})</dd>

    </h4>
</dl>

<table class="table table-bordered">
    <tr>
        <th style="width: 25%">Date</th>
        @if(!$variable_pay)
            <th style="width: 25%">Minutes</th>
        @endif

        <th style="width: 25%">Total Hours</th>
        @if($variable_pay)
            <th style="width: 25%">CCM Hours (${{$high_rate}}/Hour)</th>
            <th style="width: 25%">CCM Hours (${{$low_rate}}/Hour)</th>
        @endif
    </tr>

    <tr>
        <b>
            <td>Total Hours</td>
        </b>

        @if(!$variable_pay)
            <td>{{$total['minutes']}}</td>
        @endif

        <td>{{$total['hours']}}</td>


        @if($variable_pay)
            <td>{{$total['towards']}}</td>
            <td>{{$total['after']}}</td>
        @endif
    </tr>

    @foreach($data as $key => $value)
        <tr>
            <b>
                <td>{{$data[$key]['Date']}}</td>
            </b>

            @if(!$variable_pay)
                <td>{{$data[$key]['Minutes']}}</td>
            @endif

            <td>{{$data[$key]['Hours']}}</td>

            @if($variable_pay)
                <td>{{$variable_pay[$key]['towards']}}</td>
                <td>{{$variable_pay[$key]['after']}}</td>
            @endif
        </tr>
    @endforeach

</table>