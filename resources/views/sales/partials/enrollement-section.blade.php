<h3 style="text-align: center">Summary</h3>

<!---
Inline CSS prevents loss of table borders in emails
-->

<div class="">
    <span style="font-size: 17px">
        <u><span style="text-align: left">TOTALS</span></u>
        <br/>Enrolled <span style="color: green"> {{$data[$enrollmentSection]['enrolled'] ?? 'N/A'}} </span>
        {{--<br/>Withdrawn <span style="color: darkred"> {{$data[$enrollmentSection]['withdrawn'] ?? 'N/A'}} </span>--}}
        {{--<br/>Paused<span style="color: darkorange"> {{$data[$enrollmentSection]['paused'] ?? 'N/A'}} </span>--}}
    </span>

    <table class="table tab table-bordered myTable" style="border-collapse: collapse;border: 1px solid black; padding: 2px">
        <tr>
            <td class="tab" style="width: 12%;border: 1px solid black;  padding: 2px"></td>
            <th class="tab" style="width: 12%;border: 1px solid black; padding: 2px">{{$data['end']->format('F') . ' to Date'}}</th>
            <th class="tab" style="width: 12%;border: 1px solid black; padding: 2px">{{$data['end']->copy()->subMonthNoOverflow(1)->format('F')}}</th>
            <th class="tab" style="width: 12%;border: 1px solid black; padding: 2px">{{$data['end']->copy()->subMonthNoOverflow(2)->format('F')}}</th>
            <th class="tab" style="width: 12%;border: 1px solid black; padding: 2px">{{$data['end']->copy()->subMonthNoOverflow(3)->format('F')}}</th>
            <th class="tab" style="width: 12%;border: 1px solid black; padding: 2px">{{$data['end']->copy()->subMonthNoOverflow(4)->format('F')}}</th>
        </tr>

        @foreach($data[$enrollmentSection]['historical'] as $key => $values)
            <tr>
                <td class="tab" style="width: 30%;border: 1px solid black; padding: 2px; text-align: center"><strong>Patients {{ucwords($key)}}</strong></td>
                @foreach($values as $value)
                    <td style="border: 1px solid black; padding: 2px; text-align: center">{{$value}}</td>
                @endforeach
            </tr>
        @endforeach
    </table>
</div>
