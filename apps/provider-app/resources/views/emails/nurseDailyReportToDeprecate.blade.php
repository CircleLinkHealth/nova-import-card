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
    <b>%CCM Time<span style="color: red;">* </span>:</b> {{ $performance }}% of {{$date->englishDayOfWeek}}'s ({{$date->format('m/d')}}) time was CCM eligible care time.
</p>

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
<p><span style="color: red;">*</span> Please note %CCM time is a reference metric. If curious, you can review how our variable pay rate works <a href="https://docs.google.com/document/d/1rW4W1vbtK3Kn0SVsp9Oi34qFzfgiE7Ac9DfsbcffCUE/edit?usp=sharing">here</a>.</p>
</body>
