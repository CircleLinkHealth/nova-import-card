<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Observers;

use CircleLinkHealth\CcmBilling\Domain\Patient\ForcePatientChargeableService;
use CircleLinkHealth\CcmBilling\Entities\PatientForcedChargeableService;
use CircleLinkHealth\CcmBilling\ValueObjects\ForceAttachInputDTO;

class PatientForcedChargeableServiceObserver
{
    public function deleted(PatientForcedChargeableService $service)
    {
        ForcePatientChargeableService::handleObserverEvents(
            (new ForceAttachInputDTO())->setPatientUserId($service->patient_user_id)
                ->setMonth($service->chargeable_month)
                ->setActionType($service->action_type)
                ->setChargeableServiceId($service->chargeable_service_id)
                ->setIsDetaching(true)
                ->setEntryCreatedAt($service->created_at)
        );
    }

    public function saved(PatientForcedChargeableService $service)
    {
        ForcePatientChargeableService::handleObserverEvents(
            (new ForceAttachInputDTO())->setPatientUserId($service->patient_user_id)
                ->setMonth($service->chargeable_month)
                ->setActionType($service->action_type)
                ->setChargeableServiceId($service->chargeable_service_id)
        );
    }
}
