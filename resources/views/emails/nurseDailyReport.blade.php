<body>
<p>
    Dear {{ $name }},
</p>
<p>
    Thanks for providing care on the CircleLink platform on {{$date->englishDayOfWeek}}
    , {{$date->englishMonth}} {{$date->day}}, {{$date->year}}!
</p>
<p>
    Yesterday you worked {{$actualHours}} hours out of {{$committedHours}} hours committed:
</p>

<ul>
    <li><b>Attendance rate<span style="color: red;">*</span>:</b>&nbsp;{{$attendanceRate}}%</li>
    <li><b>Calls Completion rate<span style="color: red;">**</span>:</b>&nbsp;{{$callsCompletionRate}}%</li>
    <li><b>Efficiency Index (70 - 100 is goal, higher better)<span style="color: red;">***</span>:</b>&nbsp;{{$efficiencyIndex}}</li>
    <li><b>Total time in system on {{$date->englishDayOfWeek}} ({{$date->format('m/d')}}
            ):</b>&nbsp;{{ $totalTimeInSystemOnGivenDate }}</li>
</ul>


<p>
    Your progress this month:
</p>
<ul>
    <li>
        <b>%age case load complete:</b>&nbsp;{{$caseLoadComplete}}%
    </li>
    <li>
        <b>Est. hours to complete monthly case load:</b>&nbsp;{{$caseLoadNeededToComplete}} hrs
    </li>
    <li>
        <b>Projected hours left in month<span style="color: red;">****</span></b>&nbsp;{{$projectedHoursLeftInMonth}} hrs
    </li>
    <li>
        <b>Hours deficit or surplus:</b>&nbsp;@if($surplusShortfallHours > 0)<span style="color: green">{{$surplusShortfallHours}}
            hrs Surplus</span>@elseif($surplusShortfallHours < 0)<span style="color: red">{{abs($surplusShortfallHours)}}
            hrs Deficit</span>@endif
    </li>
    <li>
        <b>Avg. hours worked in last 10 sessions:</b> {{$avgHoursWorkedLast10Sessions}} hrs
    </li>
    <li>
        <b>Total time in system this month:</b> {{ $totalTimeInSystemThisMonth }}
    </li>
</ul>

@if ($nextUpcomingWindowLabel)
    <p>
        <b><u>Reminder:</u> {{ $nextUpcomingWindowLabel }}
            you are scheduled for {{ $totalHours }} total hours
            between {{$windowStart}}
            and {{$windowEnd}}


            <br>(Remember to provide 3 week’s notice if you need to cut your hours, thanks!)</b>
    </p>
@endif

<p>

</p>

<p>
    If you have any questions please reach out to your day-to-day contact at CircleLink Health via Slack (Direct
    Messaging),
    e-mail or phone.
</p>

<p>
    Have a great day and keep up the good work!
</p>

<p>
    CircleLink Team
</p>
<p></p>
<p><span style="color: red;">*</span> %age of the hours committed that you worked</p>

<p><span style="color: red;">**</span> %age of assigned calls that you completed.</p>

<p><span style="color: red;">***</span> Calculated by comparing your total time in system vs. the time you would have spent if each single successful call took 20 minutes of total CCM time (talk + prep/notes); and unsuccessful calls took 3 minutes of total CCM time.</p>

<p><span style="color: red;">****</span> Shows hours left in month based on your average hours worked per committed session in your last 10 sessions.</p>

</body>