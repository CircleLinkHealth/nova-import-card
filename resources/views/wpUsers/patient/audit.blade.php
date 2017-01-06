<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"
      integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous"
      xmlns="http://www.w3.org/1999/html">

<div class="page-header">
    <h3>{{$data['name']}} (Provider: {{$data['provider']}})
        <br /></h3>
     <h4>   <span style="color: #50b2e2"> CircleLink Health </span> Patient Audit Report for {{$data['month']}}
        <small>({{Carbon\Carbon::now()->toDayDateTimeString()}})</small></h4>

</div>

<div>

    <div class="container">
        <table class="table table-bordered">
            <tr>
                <th style="width: 10%">Date</th>
                <th style="width: 10%">Total Time (mins)</th>
                <th style="width: 40%">Notes</th>
                <th style="width: 40%">Activities Performed</th>
            </tr>

            @foreach($data['daily'] as $key => $val)
                <tr>
                    <td>{{$key}}</td>
                    <td>{{$val['ccm']}}</td>
                    <td>
                        @foreach($val['notes'] as $note)
                            <b>By {{$note['performer']}} at {{$note['time']}}:</b> {{$note['body']}} <br/>
                        @endforeach
                    </td>
                    <td>{{$val['activities']}}</td>
                </tr>
            @endforeach
        </table>
    </div>
</div>