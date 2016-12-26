<div>
    <h3>Overall Summary</h3>

    <p>
        Last week at your offices CircleLink nurses placed {{$data[$rangeSection]['no_of_call_attempts']}}
        calls, including {{$data[$rangeSection]['no_of_successful_calls']}} successful phone sessions, totaling
        {{$data[$rangeSection]['total_ccm_time']}} care hours. We also collected
        {{$data[$rangeSection]['no_of_biometric_entries']}} vitals readings and our nurses forwarded
        {{$data[$rangeSection]['no_of_forwarded_notes']}} notifications to you.
    </p>

    <p>
        You can see a list of forwarded notes for your patients <a
                href="{{$data[$rangeSection]['link_to_notes_listing']}}">here</a>,
        including {{$data[$rangeSection]['no_of_forwarded_emergency_notes']}} notifications that your patient is
        in
        the ER/Hospital.
    </p>

</div>