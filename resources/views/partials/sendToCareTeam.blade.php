<div class="form-group">
    <div class="col-sm-12">
        <label>Send To</label>
    </div>
    <div class="col-sm-12">
        <input type="checkbox" id="notify-careteam" name="notify_careteam">
        <label for="notify-careteam"><span> </span>Provider/CareTeam</label>
    </div>
    <div class="col-sm-12">
        <input type="checkbox" id="notify-careteam" name="notify_careteam">
        <label for="notify-careteam"><span> </span>Provider/CareTeam</label>
    </div>
    <div class="col-sm-12">
        <div class="form-group">
            @if(count($patient->care_team_receives_alerts) > 0)
                This will
                notify: @foreach($patient->care_team_receives_alerts as $carePerson){{ ($loop->first ? '' : ', ') . $carePerson->user->fullName }}@endforeach
            @else
                The CareTeam is empty. Please visit
                <strong>{{link_to_route('patient.careplan.print', 'View CarePlan', ['patientId' => $patient->id])}}</strong>
                to add Care Providers to the CareTeam.
            @endif
        </div>
    </div>
</div>