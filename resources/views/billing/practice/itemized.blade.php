<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"
      integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<div class="container-fluid">

    <div class="page-header">
        <h1>CircleLink Health
            <small>CCM BILLING REPORT <b>{{$name}}</b> ({{$month}})</small>
        </h1>
    </div>

    <table class="table table-bordered">
        <tr>
            <th style="width: 20%">Patient Name</th>
            <th style="width: 20%">DOB</th>
            <th style="width: 20%">CCM Mins</th>
            <th style="width: 20%">Condition I (Code)</th>
            <th style="width: 20%">Condition II (Code)</th>
        </tr>

        @if(isset($patientData))
            @foreach($patientData as $data)

                <tr>
                    <td>{{$data['name']}}</td>
                    <td>{{$data['dob']}}</td>
                    <td>{{$data['ccm_time']}}</td>
                    <td>{{$data['problems0']}}</td>
                    <td>{{$data['problems1'] ?? 'N/A'}}</td>
                </tr>
            @endforeach
        @endif

    </table>
</div>