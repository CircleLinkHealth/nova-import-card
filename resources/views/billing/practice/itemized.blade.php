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
            <th>Location</th>
            <th>Patient Name</th>
            <th>DOB</th>
            <th>Billing Code</th>
            <th>CCM Mins</th>
            <th>BHI Mins</th>
            <th>CCM Problem Code(s)</th>
            @if($data->getEnableAllProblemCodesColumnns())
                <th>All CCM Conditions</th>
            @endif
            <th>BHI Code(s)</th>
            @if($data->getEnableAllProblemCodesColumnns())
                <th>All BHI Conditions</th>
            @endif

        </tr>

        @if(isset($patientData))
            @foreach($patientData as $data)
                <tr>
                    <td>{{$data->getProvider()}}</td>
                    <td>{{$data->getLocationName()}}</td>
                    <td>{{$data->getName()}}</td>
                    <td>{{$data->getDob()}}</td>
                    <td>{{$data->getBillingCodes()}}</td>
                    <td>{{$data->getCcmTime()}}</td>
                    <td>{{$data->getBhiTime()}}</td>
                    <td>{{$data->getCcmProblemCodes()}}</td>
                    @if($data->getEnableAllProblemCodesColumnns())
                        <td>{{$data->getAllCcmProblemCodes()}}</td>
                    @endif
                    <td>{{$data->getBhiCodes()}}</td>
                    @if($data->getEnableAllProblemCodesColumnns())
                        <td>{{$data->getAllBhiCodes()}}</td>
                    @endif
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