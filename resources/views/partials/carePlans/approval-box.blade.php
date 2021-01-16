@if(isset($patient) && $patient instanceof CircleLinkHealth\SharedModels\Entities\User && $patient->carePlan)
    @if(! auth()->guest() && auth()->user()->isParticipant() && $patient->carePlan->status != CircleLinkHealth\SharedModels\Entities\CarePlan::PROVIDER_APPROVED)
        <div class="col-xs-12">
            <div class="pull-right print-row text-right" style="background: hsla(10, 50%, 50%, .10); padding: 10px">
                <i class="fas fa-exclamation" style="color: red"></i> This Care Plan is pending Dr. approval
            </div>
        </div>
    @elseif($patient->carePlan->status === CircleLinkHealth\SharedModels\Entities\CarePlan::PROVIDER_APPROVED && $patient->carePlan->providerApproverUser && $patient->carePlan->provider_date && $patient->getCcmStatus() !== 'patient_rejected')
        <div class="col-xs-12">
            <div class="pull-right print-row text-right">
                {{ $patient->getCcmStatus() == 'patient_rejected' ? 'Rejected' : ($patient->patientIsUPG0506() ? 'Created' : 'Approved') }}
                on {{$patient->carePlan->provider_date->format('m/d/Y')}}
                at {{$patient->carePlan->provider_date->setTimezone($patient->timezone ?? 'America/New_York')->format('g:i A T')}}
                by {{ $patient->carePlan->providerApproverUser->getFullName() }}
                @if($patient->carePlan->wasApprovedViaNurse()) via {{$patient->carePlan->getNurseApproverName()}} @endif
            </div>
        </div>
    @endif
@endif
