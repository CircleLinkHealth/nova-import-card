<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Listeners;

use CircleLinkHealth\CcmBilling\Contracts\ProvidesAttestationData;
use CircleLinkHealth\CcmBilling\Domain\Patient\AttestPatientProblems;

class CreateAttestationRecords
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(ProvidesAttestationData $event)
    {
        (new AttestPatientProblems())
            ->problemsToAttest($event->getProblemIds())
            ->fromAttestor($event->getAttestorId())
            ->forCall($event->getCallId())
            ->forAddendum($event->getAddendumId())
            ->forMonth($event->getChargeableMonth())
            ->forPms($event->getPmsId())
            ->createRecords();
    }
}
