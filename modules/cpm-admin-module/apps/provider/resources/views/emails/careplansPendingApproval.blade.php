<body>
<p>
    Dear @if($recipient->isProvider())Dr. @endif{{ $recipient->getFullName() }},
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
    <a href="https://www.careplanmanager.com/">
        login here</a>. Then, on the homepage, click "Approve Now" in the “Pending Care Plans” list, for the first patient you wish to approve:
</p>

<img src="{{ $message->embed(public_path('/img/emails/careplan-pending-approvals/approval-box.png')) }}" alt="Approve CarePlans table example image.">

<p>
    You will be taken to the Care Plan page where you will review the care plan (below):
</p>

<img src="{{ $message->embed(public_path('/img/emails/careplan-pending-approvals/view-care-plan-example.png')) }}" alt="View CarePlan example image.">

<p>
    If you agree with the care plan, please click the "Approve and View Next" button in the top right, and you will be taken to the next care plan to approve.
</p>

<p>
    Please make any changes by clicking the green “edit” icons in each of the care plan’s blue section headers.
</p>

<p>
    Alternatively, you can upload your own PDF care plan using the "Upload PDF" button.
    (<b><u>NOTE:</u></b> <em>Please make sure uploaded PDF care plans conform to Medicare requirements.</em>)
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


