<body>
<p>
    Dear Dr. {{ $drName }},
</p>

<p>
    We are delighted to report
    <a href="https://www.careplanmanager.com/manage-patients/listing">
        {{ $numberOfCareplans }}
        care plans awaiting your approval
    </a>.
</p>

<p>
    Thank you for using CircleLink Health for Chronic Care Management!
</p>

<p>
    To review and approve, simply
    <a href="https://www.careplanmanager.com/manage-patients/listing">
        view patient list here
    </a>,
    then filter the Patient List to "Approve Now" using the filter header in the "CarePlan Status" column:
</p>

<img src="{{ $message->embed(public_path('/img/patient-listing-example.png')) }}" alt="Patient Listing example image.">
<p>
    Click the "Approve now" link (above) for a patient of your choice and you will be taken to the Care Plan page where
    you will review the care plan (below).
</p>

<img src="{{ $message->embed(public_path('/img/careplan-example.png')) }}" alt="CarePlan example image.">

<p>
    If you agree with the care plan, please click the "Approve Care Plan" button in the top right, and you will be
    taken back to patient list.
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

</body>


