<div class="form-group">
    <div class="col-sm-12">
        <label>Send To:</label>
    </div>
    <div class="col-sm-12">
        <input type="checkbox" id="notify-circlelink-support" name="notify_circlelink_support" value="1">
        <label for="notify-circlelink-support"><span> </span>{{$patient->primaryPractice->saasAccountName()}}
            Support</label>
    </div>
    <div class="col-sm-12">
        @empty($patient->primaryPractice->cpmSettings()->notesChannels())
            <b>This Practice has <em>Forwarded Note Notifications</em> turned off. Please notify CirleLink support.</b>
        @else
            @empty($patient->care_team_receives_alerts)
                <p style="color: red;">
                    No provider selected to receive email alerts. Use the add ("+" sign) or edit (pencil) icons in
                    the
                    <strong>{{link_to_route('patient.careplan.print', '"Care Team"', ['patientId' => $patient->id])}}</strong>
                    section of
                    the {{link_to_route('patient.careplan.print', 'View CarePlan', ['patientId' => $patient->id])}}
                    page to
                    add or edit providers to receive email alerts.
                </p>
            @else
                <input type="checkbox" id="notify-careteam" name="notify_careteam"
                       @empty($patient->primaryPractice->cpmSettings()->notesChannels()) disabled="disabled"
                       @endempty value="1">
                <label for="notify-careteam"><span> </span>Provider/CareTeam
                    (
                    <b>Notifies:</b>
                    <span id="who-is-notified">{{ $notifies_text }}</span>
                    <b><u>via</u></b>{{ $note_channels_text }}
                    )
                </label>
            @endempty
        @endempty

    </div>
</div>