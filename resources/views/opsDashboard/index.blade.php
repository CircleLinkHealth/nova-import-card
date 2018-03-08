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
            <th>Daily</th>
            <th>Weekly</th>
            <th>Monthly</th>
            <th>Total</th>
        </tr>
        <tr>
            <td>Enrolled: {{$totals['dayCount']['enrolled']}}</td>
            <td>Enrolled: {{$totals['weekCount']['enrolled']}}</td>
            <td>Enrolled: {{$totals['monthCount']['enrolled']}}</td>
            <td>Enrolled: {{$totals['totalCount']['enrolled']}}</td>
        </tr>
        <tr>
            <td type="number" value="{{$totals['dayCount']['pausedPatients']}}">G0506 Hold: {{$totals['dayCount']['gCodeHold']}}</td>
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
</div>
<br><hr><br>
<div align="center">
    <form action="">
        <label>Generate Paused Patient List</label>
        <br>
        From:
        <input type="date" name="bday">
        To:
        <input type="date" name="bday">

        <br>
        <input align="center" type="submit" value="Submit">
    </form>
</div>
<br><hr><br>
<div>
    <table>
        <label>Patient Stats by Practice</label>
        <form action="">
            <br>
            <select name="Active Practices">
                <option value="">Active Practices</option>
                <option value="volvo">Volvo</option>
                <option value="saab">Saab</option>
                <option value="fiat">Fiat</option>
                <option value="audi">Audi</option>
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
</div>

