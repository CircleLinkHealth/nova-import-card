<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

<div class="page-header">
    <h1>{{$data['program_name']}}
        <small><span style="color: #50b2e2"> CircleLink Health </span> Account Status Report
            <b>({{Carbon\Carbon::now()->toDayDateTimeString()}})</b></small>
    </h1>
</div>

<div>

</div>

<dl class="dl-horizontal">
    <h4>
        <dt>Current Cumulative:</dt>
        <dt>Enrolled <span style="color: green"> {{$data['count'][0]['total'] ?? 'N/A'}} </span></dt>
        <dt>Withdrawn <span style="color: darkred"> {{$data['count'][2]['total'] ?? 'N/A'}} </span></dt>
        <dt>Paused<span style="color: darkorange"> {{$data['count'][1]['total'] ?? 'N/A'}} </span></dt>

    </h4>
</dl>


<table class="table table-bordered">
    <tr>
        <th>Type</th>
        <th>{{$data['t0start'] . ' to Date'}}</th>
        @if($data['withMOM'])
            <th>{{$data['t1start']}}</th>
        @endif
    </tr>

    <?php  ?>
    @foreach($data['diff'] as $key => $value)
        <tr>
            <td>{{ucwords($key) . ' during period'}}</td>
            <td>{{$data['current'][$key]}}</td>
            @if($data['withMOM'])
                <td>{{$data['last'][$key]}}</td>
            @endif
        </tr>
    @endforeach

    {{--@foreach($data['provider_data'] as $key => $value)--}}

    {{--<tr>--}}
    {{--<td>{{$data['program_name']}}</td>--}}
    {{--<td>{{'Total ' . $data['program_name']}}</td>--}}
    {{--<td>{{$key}}</td>--}}
    {{--        <td>{{$data['current'][$key]}}</td>--}}
    {{--<td>{{$data['current'][$key]}}</td>--}}
    {{--        <td>{{$data['data'][$key]['percent']}}</td>--}}
    {{--<td>{{$data['last'][$key]}}</td>--}}
    {{--<td>{{$data['last'][$status]}}</td>--}}
    {{--</tr>--}}
    {{--@endforeach--}}


</table>
