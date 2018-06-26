<link href="{{mix('/css/bootstrap.min.css')}}" rel="stylesheet">

<div class="container-fluid">

    <div class="page-header">
        <h1>CircleLink Health
            <small>CCM BILLING REPORT <b>{{$name}}</b> ({{$month}})</small>
        </h1>
    </div>

    <table class="table table-bordered">
        <tr>
            <th>Provider Name</th>
            <th>Patient Name</th>
            <th>DOB</th>
            <th>Billing Code</th>
            <th>CCM Mins</th>
            <th>Condition I</th>
            <th>Condition I Code</th>
            <th>Condition II</th>
            <th>Condition II Code</th>

        </tr>

        @if(isset($patientData))
            @foreach($patientData as $data)

                <tr>
                    <td>{{$data['provider']}}</td>
                    <td>{{$data['name']}}</td>
                    <td>{{$data['dob']}}</td>
                    <td>{{$data['billing_codes']}}</td>
                    <td>{{$data['ccm_time']}}</td>
                    <td>{{$data['problem1']}}</td>
                    <td>{{$data['problem1_code']}}</td>
                    <td>{{$data['problem2']}}</td>
                    <td>{{$data['problem2_code']}}</td>
                </tr>
            @endforeach
        @endif

    </table>
</div>