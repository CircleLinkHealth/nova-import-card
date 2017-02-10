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
            <td style="width: 12%"></td>
            <th style="width: 12%">{{$start->format('F') . ' to Date'}}</th>
            <th style="width: 12%">{{$start->subMonthNoOverflow()->format('F')}}</th>
            <th style="width: 12%">{{$start->subMonthNoOverflow()->format('F')}}</th>
            <th style="width: 12%">{{$start->subMonthNoOverflow()->format('F')}}</th>
            <th style="width: 12%">{{$start->subMonthNoOverflow()->format('F')}}</th>
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