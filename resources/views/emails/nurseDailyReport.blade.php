<body>
<p>
    Dear {{ $name }},
</p>
<p>
    Thanks for providing care on the CircleLink platform today!
</p>
<p>
    Here’s a report on your performance, time worked and earnings:
</p>

<p>
    <b>Performance:</b> {{ $performance }}% of today's time was CCM eligible care time.
</p>

<p>
    <b>Total time in system today:</b> {{ $totalTimeInSystemToday }}
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
    If you have any questions please reach out to your day-to-day contact at CircleLink Health via direct messaging
    Slack,
    e-mail or phone.
</p>

<p>
    Have a great night and keep up the good work!
</p>

<p>
    CircleLink Team
</p>
</body>