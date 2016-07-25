@extends('partials.providerUI')

@section('title', 'Patient Listing')
@section('activity', '')

@section('content')

    <div>
        <h1>Nurse Call List</h1>
        <table>
            <tr>
                <th>Nurse</th>
                <th>Full Name</th> <!-- first_name + last_name -->
                <!-- <th>Patient First Name</th> -->
                <!-- <th>Patient Last Name</th> -->
                <th>DOB</th>
                <th>Contact Window Start</th>
                <th>Contact Window End</th>
                <th>Call Center Status</th>
                <th>Status</th>
                <!-- <th>Attempt Notes</th> -->
                <th>Last Date called</th>
                <th>CCM Time to date</th>
                <th># success</th>
                <th>Provider</th>
                <th>Program</th>
            </tr>
        </table>
    </div>

@stop