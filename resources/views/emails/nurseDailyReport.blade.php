<body>
<p>
    Dear {{ $name }},
</p>
<p>
    Thanks for providing care on the CircleLink platform on {{$date->englishDayOfWeek}}, {{$date->englishMonth}} {{$date->day}}, {{$date->year}}!
</p>
<p>
    Here’s a report on your performance, time worked and earnings:
</p>

<ul>
    <li><b>Attendance/calls completion rate:<span style="color: red;">*</span></b>&nbsp;{{$completionRate}}%</li>
    <li><b>Efficiency Index (100 is goal):<span style="color: red;">**</span></b>&nbsp;{{$efficiencyIndex}}</li>
    <li><b>Total time in system on {{$date->englishDayOfWeek}} ({{$date->format('m/d')}}):</b>&nbsp;{{ $totalTimeInSystemOnGivenDate }}</li>
</ul>


<p>
    Here’s a report on your case load progress this month:
</p>
<ul>
    <li>
        <b>Est. hours to complete monthly case load:</b>&nbsp;{{$caseLoadNeededToComplete}} hrs
    </li>
    <li>
        <b>Hours committed rest of month:</b>&nbsp;{{$hoursCommittedRestOfMonth}} hrs
    </li>
    <li>
        <b>Hours deficit or surplus:</b>&nbsp;@if($surplusShortfallHours > 0)<span style="color: green">{{$surplusShortfallHours}} hrs Surplus</span>@elseif($surplusShortfallHours < 0)<span style="color: red">{{abs($surplusShortfallHours)}} hrs Deficit</span>@endif
    </li>
    <li>
        <b>%age case load complete:</b>&nbsp;{{$caseLoadComplete}}%
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
    If you have any questions please reach out to your day-to-day contact at CircleLink Health via Slack (Direct Messaging),
    e-mail or phone.
</p>

<p>
    Have a great day and keep up the good work!
</p>

<p>
    CircleLink Team
</p>
<p></p>
<p><span style="color: red;">*</span> Shows the greater of i) %age of the hours committed that you worked; or ii) %age of assigned calls that you completed.</p>

<p><span style="color: red;">**</span> ”100” means that successful calls take 15 minutes and unsuccessful calls take 4 minutes. Over 100 means you are more efficient and less than 100 means less efficient than this. </p>

</body>