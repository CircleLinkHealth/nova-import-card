<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use Cache;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Repositories\PatientWriteRepository;
use Illuminate\Support\Collection;

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

        $hasCcm = false;
        $hasPcm = false;

        /** @var Collection $candidates */
        $candidates = $chargeableServices
            ->filter(
                function ($service) use ($summary) {
                    return $this->shouldAttachChargeableService($service, $summary);
                }
            )
            ->each(
                function ($entry) use (&$hasCcm, &$hasPcm) {
                    if (ChargeableService::CCM === $entry->code) {
                        $hasCcm = true;
                    }
                    if (ChargeableService::PCM === $entry->code) {
                        $hasPcm = true;
                    }
                }
            );

        //    if patient is eligible for both PCM and CCM we choose CCM
        if ($hasCcm && $hasPcm) {
            $candidates = $candidates->filter(
                function ($service) {
                    return ChargeableService::PCM !== $service->code;
                }
            );
        }

        $attach = $candidates
            ->map(
                function ($service) {
                    return $service->id;
                }
            )
            ->values()
            ->all();

        return $this->attachChargeableService($summary, $attach);
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

        $needsQA = [];
        $hasBhi  = $summary->hasServiceCode(ChargeableService::BHI);
        if ($hasBhi && $summary->bhiAttestedProblems()->isEmpty()) {
            $needsQA[] = 'Patient has BHI service but 0 BHI attested conditions.';
        }

        $hasCcm = $summary->hasServiceCode(ChargeableService::CCM);
        if ($hasCcm && $summary->ccmAttestedProblems()->isEmpty()) {
            $needsQA[] = 'Patient has CCM service but 0 CCM attested condition';
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
     * Decide whether or not to attach a chargeable service to a patient summary.
     *
     * @return bool
     */
    private function shouldAttachChargeableService(ChargeableService $service, PatientMonthlySummary $summary)
    {
        switch ($service->code) {
            case ChargeableService::BHI:
                return $summary->bhi_time >= self::MINUTES_20;
            case ChargeableService::CCM:
            case ChargeableService::GENERAL_CARE_MANAGEMENT:
                return $summary->ccm_time >= self::MINUTES_20;
            case ChargeableService::PCM:
                return $summary->ccm_time >= self::MINUTES_30;
            case ChargeableService::CCM_PLUS_40:
                return $summary->ccm_time >= self::MINUTES_40;
            case ChargeableService::CCM_PLUS_60:
                return $summary->ccm_time >= self::MINUTES_60;
            case ChargeableService::SOFTWARE_ONLY:
                return 0 == $summary->timeFromClhCareCoaches();
            default:
                return false;
        }
    }

    /**
     * Is it ok for the system to process this record?
     */
    private function shouldNotTouch(PatientMonthlySummary $summary): bool
    {
        return (bool) $summary->actor_id;
    }
}
