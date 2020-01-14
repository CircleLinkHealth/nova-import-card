@if($patient->carePlan)
    @if(auth()->user()->isParticipant() && $patient->carePlan->status != CircleLinkHealth\CarePlanModels\Entities\CarePlan::PROVIDER_APPROVED)
        <div class="col-xs-12">
            <div class="pull-right print-row text-right" style="background: hsla(10, 50%, 50%, .10); padding: 10px">
                <i class="fas fa-exclamation" style="color: red"></i> This Care Plan is pending Dr. approval
            </div>
        </div>
    @elseif($patient->carePlan->provider_approver_id && $patient->carePlan->provider_date)
        <div class="col-xs-12">
            <div class="pull-right print-row text-right">
                {{ $patient->getCcmStatus() == 'patient_rejected' ? 'Rejected' : 'Approved' }}
                on {{$patient->carePlan->provider_date->format('m/d/Y')}}
                at {{$patient->carePlan->provider_date->setTimezone($patient->timezone ?? 'America/New_York')->format('g:i A T')}}
                by {{ $patient->getCcmStatus() == 'patient_rejected' ? \CircleLinkHealth\Customer\Entities\User::withTrashed()->find($patient->carePlan->user_id)->display_name : \CircleLinkHealth\Customer\Entities\User::withTrashed()->find($patient->carePlan->provider_approver_id)->display_name}}
            </div>
        </div>
    @endif
@endif

