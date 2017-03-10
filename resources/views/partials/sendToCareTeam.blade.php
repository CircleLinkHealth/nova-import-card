<div class="form-group">
    <div class="col-sm-12">
        <label>Send To</label>
    </div>
    <div class="col-sm-12">
        <input type="checkbox" id="notify-circlelink-support" name="notify_circlelink_support" value="1">
        <label for="notify-circlelink-support"><span> </span>CircleLink Support</label>
    </div>
    <div class="col-sm-12">
        @if(count($patient->care_team_receives_alerts) > 0)
            <input type="checkbox" id="notify-careteam" name="notify_careteam" value="1">
            <label for="notify-careteam"><span> </span>Provider/CareTeam
                (Notifies: @foreach($patient->care_team_receives_alerts as $carePerson){{ ($loop->first ? '' : ', ') . $carePerson->fullName }}@endforeach)
            </label>
        @else
            <p style="color: red;">
                No provider selected to receive alerts. Use the add or edit icons in the
                <strong>{{link_to_route('patient.careplan.print', 'View CarePlan', ['patientId' => $patient->id])}}</strong>
                section of the View CarePlan page to add or edit providers to receive alerts.
            </p>
        @endif
    </div>
</div>