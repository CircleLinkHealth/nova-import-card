<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"
      integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

<div class="page-header">
    <h1>CircleLink Health
        <small>Itemized Session Details</small>
    </h1>
</div>


<dl class="dl-horizontal">
    <h3>
        <dt>Duration</dt>
        <dd>{{$date_start}} to {{$date_end}}</dd>

        <dt>Billable Time</dt>
        <dd>{{$nurse_billable_time}}</dd>

        @if($hasAddedTime)

            <dt>Extras:</dt>
            <dd>{{$manual_time_notes}}: {{$manual_time}} (${{$manual_time_amount}})</dd>

        @endif

        <dt>Invoice Amount</dt>
        <dd>{{$total_billable_amount}} ({{$total_billable_rate}}/hr)</dd>

    </h3>
</dl>

<table class="table table-bordered">
    <tr>
        <th>Date</th>
        <th>Minutes</th>
        <th>Hours</th>
    </tr>
    @foreach($data as $key => $value)

        <tr>
            <td>{{$data[$key]['Date']}}</td>
            <td>{{$data[$key]['Minutes']}}</td>
            <td>{{$data[$key]['Hours']}}</td>
        </tr>
    @endforeach
</table>