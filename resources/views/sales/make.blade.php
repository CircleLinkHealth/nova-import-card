<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"
      integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

<div class="page-header">
    <h1>{{$data['program_name']}}
        <small>Sales Report <b>({{Carbon\Carbon::now()->toDayDateTimeString()}})</b></small>
    </h1>
</div>


<table class="table table-bordered">
    <tr>
        <th>Program</th>
        <th>Provider</th>
        <th>Status</th>
        {{--<th>This Month (%)</th>--}}
        <th>This Month (#) {{$data['t0start'] . ' to ' . $data['t0end']}}</th>
        {{--<th>Last Month (%)</th>--}}
        <th>Last Month (#) {{$data['t1start'] . ' to ' . $data['t1end']}}</th>
        <th>Total This Month</th>
    </tr>
    {{--array_sum($data['current'])--}}
    @foreach($data['data'] as $key => $value)


    <tr>
        <td>{{$data['program_name']}}</td>
        <td>{{'Total ' . $data['program_name']}}</td>
        <td>{{$key}}</td>
{{--        <td>{{$data['current'][$key]}}</td>--}}
        <td>{{$data['current'][$key]}}</td>
{{--        <td>{{$data['data'][$key]['percent']}}</td>--}}
        <td>{{$data['last'][$key]}}</td>
        {{--<td>{{$data['last'][$status]}}</td>--}}
    </tr>
    @endforeach


</table>
