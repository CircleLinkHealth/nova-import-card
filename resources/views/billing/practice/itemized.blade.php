<link href="{{asset('/css/bootstrap.min.css')}}" rel="stylesheet">

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
            <th>BHI Mins</th>
            <th>CCM Problem Codes</th>
            <th>BHI Problem</th>
            <th>BHI Code</th>

        </tr>

        @if(isset($patientData))
            @foreach($patientData as $data)

                <tr>
                    <td>{{$data->getProvider()}}</td>
                    <td>{{$data->getName()}}</td>
                    <td>{{$data->getDob()}}</td>
                    <td>{{$data->getBillingCodes()}}</td>
                    <td>{{$data->getCcmTime()}}</td>
                    <td>{{$data->getBhiTime()}}</td>
                    <td>{{$data->getCcmProblemCodes()}}</td>
                    <td>{{$data->getBhiProblem()}}</td>
                    <td>{{$data->getBhiCode()}}</td>
                </tr>
            @endforeach
        @endif

    </table>
    <br>
    @if(isset($awvPatientData))
    <table class="table table-bordered">
        <tr>
            <th>Provider Name</th>
            <th>Patient Name</th>
            <th>DOB</th>
            <th>AWV Date</th>

        </tr>
            @foreach($awvPatientData as $data)
                <tr>
                    <td>{{$data->getProvider()}}</td>
                    <td>{{$data->getName()}}</td>
                    <td>{{$data->getDob()}}</td>
                    <td>{{$data->getAwvDate()}}</td>
                </tr>
            @endforeach
    </table>
    @endif
</div>