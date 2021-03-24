<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Repositories;

use CircleLinkHealth\CcmBilling\Domain\Patient\PatientServicesToAttachForLegacyABP;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Repositories\PatientWriteRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PatientSummaryEloquentRepository
{
    const MINUTES_20 = 1200;
    const MINUTES_30 = 1800;
    const MINUTES_40 = 2400;
    const MINUTES_60 = 3600;

    /**
     * @var CallRepository
     */
    public $callRepo;
    /**
     * @var PatientWriteRepository
     */
    public $patientRepo;

    private $chargeableServicesByCode = [];

    /**
     * PatientSummaryEloquentRepository constructor.
     */
    public function __construct(PatientWriteRepository $patientRepo, CallRepository $callRepo)
    {
        $this->patientRepo = $patientRepo;
        $this->callRepo    = $callRepo;
    }

    /**
     * Attach the practice's default chargeable service to the given patient summary.
     *
     * @param $summary
     * @param array|ChargeableService|null $chargeableServiceId | The Chargeable Service Code to attach
     * @param bool                         $detach              | Whether to detach existing chargeable services, when using the sync function
     *
     * @return mixed
     */
    public function attachChargeableService($summary, $chargeableServiceId = null, $detach = false)
    {
        if ( ! $chargeableServiceId) {
            return $summary;
        }

        if (is_a($chargeableServiceId, ChargeableService::class)) {
            $chargeableServiceId = $chargeableServiceId->id;
        }

        if ( ! is_array($chargeableServiceId)) {
            $chargeableServiceId = [$chargeableServiceId];
        }
        $toSync = [];

        foreach ($chargeableServiceId as $id) {
            $toSync[$id] = [
                'is_fulfilled' => true,
            ];
        }

        $sync = $summary->chargeableServices()
            ->sync($toSync, $detach);

        if ($sync['attached'] || $sync['detached'] || $sync['updated']) {
            $class = PatientMonthlySummary::class;
            Cache::tags(['practice.chargeable.services'])->forget("${class}:{$summary->id}:chargeableServices");
            $summary->load('chargeableServices');
        }

        return $summary;
    }

    /**
     * @return mixed|PatientMonthlySummary
     */
    public function attachChargeableServices(PatientMonthlySummary $summary)
    {
        //Unfulfilled chargeable services are not included in chargeableServices relationship, so it should be empty.
        if ($this->shouldNotTouch($summary) && $summary->chargeableServices->isNotEmpty()) {
            return $summary;
        }

        $chargeableServices = $this->chargeableServicesByCode($summary);

        $candidates = PatientServicesToAttachForLegacyABP::getCollection($summary, $chargeableServices);

        $attach = $candidates
            ->map(
                function ($service) {
                    return $service['id'];
                }
            )
            ->values()
            ->all();

        return $this->attachChargeableService($summary, $attach, true);
    }

    public function detachChargeableService($summary, $chargeableServiceId)
    {
        $detached = $summary->chargeableServices()
            ->detach($chargeableServiceId);

        $summary->load('chargeableServices');

        return $summary;
    }

    /**
     * This function will set field `needs_qa` on the $summary.
     * If the $summary needs to be QA'ed by a human, approved and rejected will be set to false.
     *
     * @return PatientMonthlySummary
     */
    public function setApprovalStatusAndNeedsQA(PatientMonthlySummary $summary)
    {
        if ($this->shouldNotTouch($summary)) {
            return $summary;
        }

        $summary->autoAttestConditionsIfYouShould();
        $summary->load('attestedProblems');

        $needsQA = [];
        $hasBhi  = $summary->hasServiceCode(ChargeableService::BHI);
        if ($hasBhi && $summary->bhiAttestedProblems()->isEmpty()) {
            $needsQA[] = 'Patient has BHI service but 0 BHI attested conditions.';
        }

        $hasCcm = $summary->hasServiceCode(ChargeableService::CCM);
        if ($hasCcm && $summary->ccmAttestedProblems()->isEmpty()) {
            $needsQA[] = 'Patient has CCM service but 0 CCM attested conditions.';
        }

        $hasPcm = $summary->hasServiceCode(ChargeableService::PCM);
        if ($hasPcm && $summary->ccmAttestedProblems()->isEmpty()) {
            $needsQA[] = 'Patient has PCM service but 0 CCM attested conditions.';
        }

        if ($summary->approved && $summary->rejected) {
            $needsQA[] = 'Summary was both approved and rejected.';
        }

        if (0 == $summary->no_of_successful_calls) {
            $needsQA[] = '0 successful calls';
        }

        if ( ! $summary->patient->billingProviderUser()) {
            $needsQA[] = 'No billing provider';
        }

        if (in_array(
            $summary->patient->patientInfo->getCcmStatusForMonth($summary->month_year),
            [Patient::WITHDRAWN, Patient::PAUSED, Patient::WITHDRAWN_1ST_CALL]
        )) {
            $needsQA[] = 'Patient not enrolled.';
        }

        if ( ! empty($needsQA)) {
            $summary->needs_qa = true;
            $summary->approved = $summary->rejected = false;
            $reasonsString = implode(',', $needsQA);
            Log::info("Billing: (Legacy) PMS for patient {$summary->patient_id} needs QA for month {$summary->month_year->toDateString()} for the following reasons: $reasonsString");
        } else {
            $summary->approved = true;
            $summary->needs_qa = $summary->rejected = false;
        }

        if ($summary->isDirty(['approved', 'rejected', 'needs_qa'])) {
            $summary->save();
        }

        return $summary;
    }

    /**
     * Save the most updated sum of calls and sum of successful calls to the given PatientMonthlySummary.
     *
     * @return PatientMonthlySummary
     */
    public function syncCallCounts(PatientMonthlySummary $summary)
    {
        $summary->no_of_calls            = $this->callRepo->numberOfCalls($summary->patient_id, $summary->month_year);
        $summary->no_of_successful_calls = $this->callRepo->numberOfSuccessfulCalls(
            $summary->patient_id,
            $summary->month_year
        );

        return $summary;
    }

    private function chargeableServicesByCode(PatientMonthlySummary $summary)
    {
        if ( ! $summary->patient || ! $summary->patient->primaryPractice) {
            return collect();
        }
        $practiceId = $summary->patient->primaryPractice->id;

        if ( ! isset($this->chargeableServicesByCode[$practiceId])) {
            $this->chargeableServicesByCode[$practiceId] = $summary->patient->primaryPractice->chargeableServices->keyBy('code');
        }

        return $this->chargeableServicesByCode[$practiceId];
    }

    /**
     * Is it ok for the system to process this record?
     */
    private function shouldNotTouch(PatientMonthlySummary $summary): bool
    {
        return (bool) $summary->actor_id;
    }
}
