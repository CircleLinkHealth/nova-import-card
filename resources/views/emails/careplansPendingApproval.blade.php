<body>
<p>
    Dear @if($recipient->hasRole('provider'))Dr. @endif{{ $recipient->fullName }},
</p>

<p>
    Thank you for using CircleLink Health for Chronic Care Management!
</p>

<p>
    We are delighted to report <b style="color: #0070C0;">{{ $numberOfCareplans }} care plan(s)</b> awaiting your
    approval.
</p>


<p>
    To review and approve, simply
    <a href="https://www.careplanmanager.com/manage-patients/listing">
        view Patient List here</a>, then filter to "Approve Now" using the filter header in the "CarePlan Status"
    column. You may also want to filter for your name in the “Provider” column:
</p>

<img src="{{ $message->embed(public_path('/img/patient-listing-example.png')) }}" alt="Patient Listing example image.">
<p>
    Click the "Approve Now" link (above) for a patient of your choice and you will be taken to the Care Plan page where
    you will review the care plan (below).
</p>

<img src="{{ $message->embed(public_path('/img/careplan-example.png')) }}" alt="CarePlan example image.">

<p>
    If you agree with the care plan, please click the "Approve Care Plan" button in the top right, and you will be
    taken back to Patient List.
</p>

<p>
    Please make any changes via the “Edit Care Plan” button in top center.
</p>

<p>
    Our registered nurses will take it from here!
</p>

<p>
    Thank you again,
</p>

<p>
    CircleLink Team
</p>

<p style="color: #0070C0;">
    <b>
        <em>
            To receive this notification less (or more) frequently, please adjust your settings <a
                    href="https://www.careplanmanager.com/settings/email/create">here</a>.
        </em>
    </b>
</p>

</body>


