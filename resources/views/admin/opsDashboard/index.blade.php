@extends('partials.adminUI')

@section('content')
    @push('styles')
        <style>
            .ops-dboard-title {
                background-color: #eee;
                padding: 2rem;
            }
        </style>
    @endpush

    <div class="container">
        <h1 align="center">Patient Pipeline</h1>
        <div class="row">
            <div class="col-md-12">
                @include('admin.opsDashboard.tables.total-patients')
            </div>
        </div>
        <br>
        <hr><br>
        <div class="text-center">
            <div>
                <form action="{{route('OpsDashboard.pausedPatientList')}}">
                    <h3 class="ops-dboard-title">Generate Paused Patient List</h3>
                    <br>
                    From:
                    <input type="date" name="fromDate" required>
                    To:
                    <input type="date" name="toDate" required>

                    <br>
                    <input align="center" type="submit" value="Submit">
                </form>
            </div>

        </div>
        <br>
        <hr><br>
        <div>
            <table class="table">
                <label>Patient Stats by Practice</label>
                <form action="">
                    <br>
                    <select name="Active Practices">
                        <option value="">Active Practices</option>
                        @foreach($practices as $practice)
                            <option name="practiceId" value="{{$practice['id']}}">{{$practice['display_name']}}</option>
                        @endforeach
                    </select>
                    <br>
                    <input type="submit" value="Submit">
                </form>

                <tr>
                    <th>Daily</th>
                    <th>Weekly</th>
                    <th>Monthly</th>
                    <th>Total</th>
                </tr>
                <tr>
                    <td>Enrolled: {{$patientsByPractice['dayCount']['enrolled']}}</td>
                    <td>Enrolled: {{$patientsByPractice['weekCount']['enrolled']}}</td>
                    <td>Enrolled: {{$patientsByPractice['monthCount']['enrolled']}}</td>
                    <td>Enrolled: {{$patientsByPractice['totalCount']['enrolled']}}</td>
                </tr>
                <tr>
                    <td>G0506 Hold: {{$patientsByPractice['dayCount']['gCodeHold']}}</td>
                    <td>G0506 Hold: {{$patientsByPractice['weekCount']['gCodeHold']}}</td>
                    <td>G0506 Hold: {{$patientsByPractice['monthCount']['gCodeHold']}}</td>
                    <td>G0506 Hold: {{$patientsByPractice['totalCount']['gCodeHold']}}</td>
                </tr>
                <tr>
                    <td>Paused: {{$patientsByPractice['dayCount']['pausedPatients']}}</td>
                    <td>Paused: {{$patientsByPractice['weekCount']['pausedPatients']}}</td>
                    <td>Paused: {{$patientsByPractice['monthCount']['pausedPatients']}}</td>
                    <td>Paused: {{$patientsByPractice['totalCount']['pausedPatients']}}</td>
                </tr>
                <tr>
                    <td>Withdrawn: {{$patientsByPractice['dayCount']['withdrawnPatients']}} </td>
                    <td>Withdrawn: {{$patientsByPractice['weekCount']['withdrawnPatients']}}</td>
                    <td>Withdrawn: {{$patientsByPractice['monthCount']['withdrawnPatients']}}</td>
                    <td>Withdrawn: {{$patientsByPractice['totalCount']['withdrawnPatients']}}</td>
                </tr>
            </table>
            <form action="{{route('OpsDashboard.patientsByPractice')}}" method="GET">
                <br>
                Go to a specific day:
                <input type="date" name="dayDate" required>
                <br>
                <input align="center" type="submit" value="Submit">
            </form>
            <form action="{{route('OpsDashboard.patientsByPractice')}}" method="GET">
                <br>
                Go to a specific week:
                <input type="date" name="weekDate" required>
                <br>
                <input align="center" type="submit" value="Submit">
            </form>
            <form action="{{route('OpsDashboard.patientsByPractice')}}" method="GET">
                <br>
                Go to a specific month:
                <input type="date" name="monthDate" required>
                <br>
                <input align="center" type="submit" value="Submit">
            </form>
        </div>
    </div>
@endsection


