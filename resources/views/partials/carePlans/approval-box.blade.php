@if($patient->carePlan->provider_approver_id && $patient->carePlan->provider_date)
    <div class="col-xs-12">
        <div class="pull-right print-row text-right">
            Approved on {{$patient->carePlan->provider_date->format('m/d/Y')}}
            at {{$patient->carePlan->provider_date->setTimezone($patient->timezone ?? 'America/New_York')->format('H:i')}} {{$patient->carePlan->provider_date->setTimezone($patient->timezone ?? 'America/New_York')->format('T')}}
            by {{App\User::find($patient->carePlan->provider_approver_id)->fullName}}
        </div>
    </div>
@endif