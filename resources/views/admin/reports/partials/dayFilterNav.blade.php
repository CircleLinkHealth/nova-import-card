<ul class>
    @foreach($days as $weekDay)
        <li><a href="{{route('admin.reports.nurse.weekly.dayfilter', [\Carbon\Carbon::parse($weekDay)->toDateTimeString()])}}">{{\Carbon\Carbon::parse($weekDay)->format('D')}}</a></li>
    @endforeach
</ul>