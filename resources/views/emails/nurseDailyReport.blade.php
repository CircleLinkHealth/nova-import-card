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

<p>
    <b>Attendance/calls completion rate<span style="color: red;">* </span></b> {{$completionRate}}%
</p>
<p>
    <b>Efficiency Index (100 is goal, higher is better)<span style="color: red;">** </span></b> {{$efficiencyIndex}}
</p>
<p>
    <b>Hours behind for month<span style="color: red;">*** </span></b> {{$hoursBehind}}
<p>
    <b>Total time in system on {{$date->englishDayOfWeek}} ({{$date->format('m/d')}}):</b> {{ $totalTimeInSystemOnGivenDate }}
</p>

<p>
    <b>Total time in system this month:</b> {{ $totalTimeInSystemThisMonth }}
</p>

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

<p><span style="color: red;">***</span> Hours of work behind based on # of currently assigned patients and remaining work days in month. Assumes you’re working every biz. day, so may swing day to day if you’re not.</p>
</body>