<h3>Enrollment Summary</h3>

<?php $currentMonth = Carbon\Carbon::now()->format('F Y') ?>
<div class="">
    <h5>
        <span style="text-align: left">Current Cumulative:</span><br/>
        <ul style="list-style: none">
            <li>Enrolled <span style="color: green"> {{$data[$enrollmentSection]['enrolled'] ?? 'N/A'}} </span>
            </li>
            <li>Withdrawn <span
                        style="color: darkred"> {{$data[$enrollmentSection]['withdrawn'] ?? 'N/A'}} </span>
            </li>
            <li>Paused<span style="color: darkorange"> {{$data[$enrollmentSection]['paused'] ?? 'N/A'}} </span>
            </li>
        </ul>
    </h5>

    <table class="table table-bordered">
        <tr>
            <td>Patient Changes</td>
            <th>{{\Carbon\Carbon::now()->format('F') . ' to Date'}}</th>
            <th>{{\Carbon\Carbon::now()->subMonths(1)->format('F')}}</th>
            <th>{{\Carbon\Carbon::now()->subMonths(2)->format('F')}}</th>
            <th>{{\Carbon\Carbon::now()->subMonths(3)->format('F')}}</th>
            <th>{{\Carbon\Carbon::now()->subMonths(4)->format('F')}}</th>

        </tr>

        @foreach($data[$enrollmentSection]['historical'] as $key => $values)
            <tr>
                <td>{{$key}}</td>
                @foreach($values as $value)
                    <td>{{$value}}</td>
                @endforeach
            </tr>
        @endforeach
    </table>
</div>