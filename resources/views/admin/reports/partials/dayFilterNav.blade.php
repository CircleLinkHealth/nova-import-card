<ul class>
    @foreach($days as $weekDay)
        <li>
            <a href="{{route('admin.reports.nurse.weekly.dayfilter', [$weekDay->toDateString()])}}">
                {{$weekDay->format('D')}}
            </a>
        </li>
    @endforeach
</ul>