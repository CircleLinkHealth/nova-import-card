<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Decorators;

use CircleLinkHealth\Core\Traits\ValidatesDates;
use Carbon\Carbon;
use CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation;
use CircleLinkHealth\Eligibility\Contracts\MedicalRecordDecorator;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;

class EncountersFromAthena implements MedicalRecordDecorator
{
    use ValidatesDates;

    /**
     * @var AthenaApiImplementation
     */
    protected $api;
    /**
     * @var string|null
     */
    protected $endDate;
    /**
     * @var string|null
     */
    protected $startDate;

    /**
     * EncountersFromAthena constructor.
     *
     * @param string|null $startDate
     * @param string|null $endDate
     */
    public function __construct(AthenaApiImplementation $api)
    {
        $this->api = $api;
    }

    /**
     * @throws \Exception
     */
    public function decorate(EligibilityJob $eligibilityJob): EligibilityJob
    {
        $eligibilityJob->loadMissing('targetPatient');

        $data               = $eligibilityJob->data;
        $data['encounters'] = $this->api->getEncounters(
            $eligibilityJob->targetPatient->ehr_patient_id,
            $eligibilityJob->targetPatient->ehr_practice_id,
            $eligibilityJob->targetPatient->ehr_department_id,
            $this->getStartDate(),
            $this->getEndDate()
        );

        $lastEncounter = $this->carbon(
            collect($data['encounters']['encounters'])->sortByDesc(
                'appointmentstartdate'
            )->pluck('appointmentstartdate')->first()
        );

        if ($lastEncounter instanceof Carbon) {
            $data['last_encounter']         = $lastEncounter->toDateString();
            $eligibilityJob->last_encounter = $lastEncounter;
        }

        $eligibilityJob->data = $data;

        if ($eligibilityJob->isDirty()) {
            $eligibilityJob->save();
        }

        return $eligibilityJob;
    }

    public function getEndDate(): ?string
    {
        return $this->endDate;
    }

    public function getStartDate(): ?string
    {
        return $this->startDate;
    }

    public function setEndDate(?string $endDate): EncountersFromAthena
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function setStartDate(?string $startDate): EncountersFromAthena
    {
        $this->startDate = $startDate;

        return $this;
    }

    private function carbon($lastEncounter)
    {
        if ($this->isValidDate($lastEncounter)) {
            return Carbon::createFromFormat(Carbon::ATOM, $lastEncounter);
        }
    }
}
