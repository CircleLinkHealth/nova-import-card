@extends('partials.adminUI')

@section('content')
<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Ops Dashboard</title>


    <style>
        table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            width: 100%;
        }


        label {
            display: block;
            text-align: center;
            line-height: 150%;
            font-size: 1.85em;
        }

        td, th {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        tr:nth-child(even) {
            background-color: #dddddd;
        }
    </style>
</head>
<body>
<h1 align="center">Patient Pipeline</h1>

<div>
    <table>
        <label>CarePlan Manager Patient Totals</label>
        <tr>
            <th><a href="{{route('OpsDashboard.patientList', ['type'=> 'day'])}}" method="GET">Daily</a></th>
            <th><a href="{{route('OpsDashboard.patientList', ['type'=> 'week'])}}" method="GET">Weekly</a></th>
            <th><a href="{{route('OpsDashboard.patientList', ['type'=> 'month'])}}" method="GET">Monthly</a></th>
            <th><a href="{{route('OpsDashboard.patientList', ['type'=> 'total'])}}" method="GET">Total</a></th>
        </tr>
        <tr>
            <td>Enrolled: {{$totals['dayCount']['enrolled']}}</td>
            <td>Enrolled: {{$totals['weekCount']['enrolled']}}</td>
            <td>Enrolled: {{$totals['monthCount']['enrolled']}}</td>
            <td>Enrolled: {{$totals['totalCount']['enrolled']}}</td>
        </tr>
        <tr>
            <td>G0506 Hold: {{$totals['dayCount']['gCodeHold']}}</td>
            <td>G0506 Hold: {{$totals['weekCount']['gCodeHold']}}</td>
            <td>G0506 Hold: {{$totals['monthCount']['gCodeHold']}}</td>
            <td>G0506 Hold: {{$totals['totalCount']['gCodeHold']}}</td>
        </tr>
        <tr>
            <td>Paused: {{$totals['dayCount']['pausedPatients']}}</td>
            <td>Paused: {{$totals['weekCount']['pausedPatients']}}</td>
            <td>Paused: {{$totals['monthCount']['pausedPatients']}}</td>
            <td>Paused: {{$totals['totalCount']['pausedPatients']}}</td>
        </tr>
        <tr>
            <td>Withdrawn: {{$totals['dayCount']['withdrawnPatients']}} </td>
            <td>Withdrawn: {{$totals['weekCount']['withdrawnPatients']}}</td>
            <td>Withdrawn: {{$totals['monthCount']['withdrawnPatients']}}</td>
            <td>Withdrawn: {{$totals['totalCount']['withdrawnPatients']}}</td>
        </tr>
    </table>
    <form action="{{route('OpsDashboard.totalData')}}" method="GET">
        <br>
        Go to a specific day:
        <input type="date" name="dayDate" required>
        <br>
        <input align="center" type="submit" value="Submit">
    </form>
    <form action="{{route('OpsDashboard.totalData')}}" method="GET">
        <br>
        Go to a specific week:
        <input type="date" name="weekDate" required>
        <br>
        <input align="center" type="submit" value="Submit">
    </form>
    <form action="{{route('OpsDashboard.totalData')}}" method="GET">
        <br>
        Go to a specific month:
        <input type="date" name="monthDate" required>
        <br>
        <input align="center" type="submit" value="Submit">
    </form>
</div>
<br><hr><br>
<div align="center">
    <div>
        <form action="{{route('OpsDashboard.pausedPatientList')}}">
            <label>Generate Paused Patient List</label>
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
<br><hr><br>
<div>
    <table>
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
@endsection


