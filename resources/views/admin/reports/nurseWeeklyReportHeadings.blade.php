<tr>
    {{--Fill's empty space--}}
    <th colspan="12"><br></th>
    @foreach($days as $weekDay)
        <th colspan="12">{{$weekDay->format('l F jS Y')}}</th>
    @endforeach
</tr>
<tr>
    <th><br>Name</th>
    @foreach($days as $weekDay)
        <th><br>Assigned Calls</th>
        <th><br>Actual Calls</th>
        <th><br>Successful Calls</th>
        <th><br>Unsuccessful Calls</th>
        <th><br>Actual Hrs Worked</th>
        <th><br>Committed Hrs</th>
        <th><br>Attendance/Calls Completion Rate</th>
        <th><br>Efficiency Index</th>
        <th><br>Est. Hrs to Complete Case Load</th>
        <th><br>Hrs Committed Rest of Month</th>
        <th><br>Hrs Deficit or Surplus</th>
        <th style="border-right: solid 2px #000000;"><br>% Case Load Complete</th>
    @endforeach
</tr>

