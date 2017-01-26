<h3 style="text-align: center">Summary</h3>

<?php $start = Carbon\Carbon::parse($data['start']) ?>

<div class="">
    <span style="font-size: 17px">
        <u><span style="text-align: left">TOTALS</span></u>
        <br/>Enrolled <span style="color: green"> {{$data[$enrollmentSection]['enrolled'] ?? 'N/A'}} </span>
        <br/>Withdrawn <span style="color: darkred"> {{$data[$enrollmentSection]['withdrawn'] ?? 'N/A'}} </span>
        <br/>Paused<span style="color: darkorange"> {{$data[$enrollmentSection]['paused'] ?? 'N/A'}} </span>
    </span>

    <table class="table table-bordered myTable">
        <tr>
            <td></td>
            <th>{{\Carbon\Carbon::parse($start)->format('F') . ' to Date'}}</th>
            <th>{{\Carbon\Carbon::parse($start)->subMonths(1)->format('F')}}</th>
            <th>{{\Carbon\Carbon::parse($start)->subMonths(2)->format('F')}}</th>
            <th>{{\Carbon\Carbon::parse($start)->subMonths(3)->format('F')}}</th>
            <th>{{\Carbon\Carbon::parse($start)->subMonths(4)->format('F')}}</th>
        </tr>

        @foreach($data[$enrollmentSection]['historical'] as $key => $values)
            <tr>
                <td style="width: 30%"><strong>Patients {{ucwords($key)}}</strong></td>
                @foreach($values as $value)
                    <td>{{$value}}</td>
                @endforeach
            </tr>
        @endforeach
    </table>
</div>