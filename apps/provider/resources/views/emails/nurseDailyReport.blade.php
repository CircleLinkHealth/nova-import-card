<div class="container">
    <p>Dear {{ $name }},</p>
    <p>&nbsp;</p>
    <p>Thanks for providing care on the CircleLink platform on {{$date->format('m-d-Y')}}</p>
    <p>&nbsp;</p>
    <ol>
        <li> Work Completed Yesterday {{$date->format('m-d-Y')}}</li>
    </ol>
    <ul>

        <li>{{$actualHours}} Hours Worked out of {{$committedHours}} Hours Committed</li>
        <li>Total calls completed: {{$callsCompleted}}</li>
        <li>Total successful calls: {{$successfulCalls}}</li>
        {{--        <li>Total visits completed: __ (To be Linked to invoice page in CPM)</li>--}}
    </ul>
    <p>&nbsp;</p>
    <ol start="2">

        <li> Monthly Case Completion ({{$caseLoadComplete}}%)</li>
    </ol>
    <ul>
        <li>Monthly caseload: {{$totalPatientsInCaseLoad}} patients</li>
        @if(showNurseMetricsInDailyEmailReport($nurseUserId, 'patients_completed_and_remaining'))
            <li>Patients completed for the month: {{$completedPatients}}</li>
            <li>Patients remaining: {{$incompletePatients}}</li>
        @endif
    </ul>
    <br>
    @if(showNurseMetricsInDailyEmailReport($nurseUserId, 'efficiency_metrics'))
        <p>&nbsp;</p>
        <ol start="3">
            <li> Efficiency Metrics</li>
        </ol>
        <ul>
            <li>Average CCM time per successful patient: {{$avgCCMTimePerPatient}} minutes (goal is to stay as close to
                20 minutes as possible)
            </li>
            <ul>
                <li>Calculated by dividing your total CCM time on successful patients by the total amount of completed
                    patients for the month
                </li>
            </ul>
            <li>Average time to complete a patient: {{$avgCompletionTime}} minutes (goal is to be under 30 minutes)</li>
            <ul>
                <li>Calculated by dividing your total CPM time by the number of completed patients for the month</li>
            </ul>
        </ul>
        <p>&nbsp;</p>
    @endif

    @if(showNurseMetricsInDailyEmailReport($nurseUserId, 'enable_daily_report_metrics'))
        <ol start="4">
            <li> Scheduling and Monthly Hours</li>
        </ol>
        <ul>
            <li>Estimated time to complete case load: {{$caseLoadNeededToComplete}} hrs</li>
            <ul>
                <li>Calculated by multiplying the average time to complete a patient (above) by total remaining patients
                    and dividing by 60 minutes to get an hour total
                </li>
            </ul>
            <li>Committed hours for remainder of month: {{$hoursCommittedRestOfMonth}} hrs</li>
            <ul>
                <li>For more accuracy, enter your schedule for the entire month. Otherwise, the system estimates based
                    off the current week's hours
                </li>
            </ul>
            <li>Surplus or deficit for the remainder of month: <a
                        style="color:{{$deficitTextColor}}">{{$surplusShortfallHours}}</a> hr {{$deficitOrSurplusText}}
            </li>
            <ul>
                <li><a style="color: green">Surplus</a> indicates you are doing well for the month and are on pace to
                    successfully complete your caseload
                </li>
                <li><a style="color: red">Deficit</a> indicates you are behind in completing your caseload and need to
                    make up hours or reach out for assistance in completing your caseload
                </li>
            </ul>
            <li>Next scheduled shift: {{ $totalHours }} hours between {{$windowStart}} and {{$windowEnd}}
                on {{$nextUpcomingWindowDay}}, {{$nextUpcomingWindowMonth}}</li>
        </ul>
    @endif
    <p>If you have any questions, concerns or schedule changes, please reach out to your CLH managers over Slack.</p>
    <p>Have a great day and keep up the good work!</p>
    <p>The CircleLink Health Team</p>
</div>

