<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\PatientMonthlyBillingStatus;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use Exception;

class ProcessPatientBillingStatus
{
    private ?PatientMonthlyBillingStatus $billingStatus = null;
    private ?Carbon $month                              = null;
    private ?int $patientId                             = null;

    /**
     * @throws Exception
     */
    private function setup(): bool
    {
        if ( ! $this->patientId) {
            throw new Exception('missing patientId');
        }
        if ( ! $this->month) {
            $this->month = now();
        }
        $this->month = $this->month->startOfMonth();

        $this->billingStatus = PatientMonthlyBillingStatus::firstOrCreate(
            [
                'patient_user_id'  => $this->patientId,
                'chargeable_month' => $this->month,
            ],
            []
        );

        if ($this->shouldNotTouch()) {
            return false;
        }

        $this->billingStatus->loadMissing([
            'patientUser' => function ($q) {
                $q->with([
                    'billingProvider.user',
                    'attestedProblems' => function ($q) {
                        $q->with('ccdProblem.cpmProblem')
                            ->createdOnIfNotNull($this->month, 'chargeable_month');
                    },
                    'inboundSuccessfulCalls' => function ($q) {
                        $q->createdInMonth($this->month, 'called_date');
                    },
                    'chargeableMonthlySummaries' => function ($q) {
                        $q->createdOnIfNotNull($this->month, 'chargeable_month');
                    },
                ]);
            },
        ]);

        return true;
    }

    /**
     * @throws Exception
     */
    public function execute()
    {
        if ( ! $this->setup()) {
            return;
        }

        /** @var User $patient */
        $patient         = $this->billingStatus->patientUser;
        $autoAttestation = AutoPatientAttestation::fromUser($patient)
            ->setMonth($this->month)
            ->setPmsId(optional($patient->patientSummaryForMonth($this->month))->id);
        $autoAttestation->executeIfYouShould();

        if ($autoAttestation->unAttestedBhi()
            || $autoAttestation->unAttestedCcm()
            || $autoAttestation->unAttestedPcm()
            || $patient->chargeableMonthlySummaries->isEmpty()
            || 0 === $patient->inbound_successful_calls_count
            || ! $patient->billingProviderUser()
            || in_array($patient->getCcmStatusForMonth($this->month), [Patient::WITHDRAWN, Patient::PAUSED, Patient::WITHDRAWN_1ST_CALL])) {
            $this->billingStatus->status = 'needs_qa';
        } else {
            $this->billingStatus->status = 'approved';
        }

        if ($this->billingStatus->isDirty(['status'])) {
            $this->billingStatus->save();
        }
    }

    public function setMonth(Carbon $month): ProcessPatientBillingStatus
    {
        $this->month = $month;

        return $this;
    }

    public function setPatientId(int $patientId): ProcessPatientBillingStatus
    {
        $this->patientId = $patientId;

        return $this;
    }

    private function shouldNotTouch(): bool
    {
        return ! is_null($this->billingStatus->actor_id);
    }
}
