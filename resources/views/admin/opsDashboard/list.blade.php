<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Patient List</title>


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
<h1 align="center">Patient List</h1>
<div>
    <table>
        <tr>
            <th>Id</th>
            <th>Name</th>
            <th>Email</th>
        </tr>
        @foreach($patients as $patient)
            <tr>
                <td>{{$patient['id']}}</td>
                <td>{{$patient['display_name']}}</td>
                <td>{{$patient['email']}}</td>
            </tr>

        @endforeach
    </table>

</div>
</body>