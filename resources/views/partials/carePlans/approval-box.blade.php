@if($patient->carePlan && $patient->carePlan->provider_approver_id && $patient->carePlan->provider_date)
    <div class="col-xs-12">
        <div class="pull-right print-row text-right">
            {{ $patient->getCcmStatus() == 'patient_rejected' ? 'Rejected' : 'Approved' }} on {{$patient->carePlan->provider_date->format('m/d/Y')}}
            at {{$patient->carePlan->provider_date->setTimezone($patient->timezone ?? 'America/New_York')->format('g:i A T')}} 
            by {{ $patient->getCcmStatus() == 'patient_rejected' ? App\User::withTrashed()->find($patient->carePlan->user_id)->display_name : App\User::withTrashed()->find($patient->carePlan->provider_approver_id)->display_name}}
        </div>
    </div>
@endif

