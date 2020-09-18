<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Decorators;

use CircleLinkHealth\Core\Traits\ValidatesDates;
use CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation;
use CircleLinkHealth\Eligibility\Contracts\MedicalRecordDecorator;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use Illuminate\Support\Collection;

class DepartmentFromAthena implements MedicalRecordDecorator
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

        /** @var Collection $departments */
        $departments = \Cache::remember("athena:practice_id:{$eligibilityJob->targetPatient->ehr_practice_id}:departments", 2, function () use ($eligibilityJob) {
            $response = $this->api->getDepartments($eligibilityJob->targetPatient->ehr_practice_id);

            return array_key_exists('departments', $response) ? collect($response['departments']) : collect();
        });

        if ($deptName = $departments->firstWhere('departmentid', '=', $eligibilityJob->targetPatient->ehr_department_id)['name'] ?? null) {
            $data                    = $eligibilityJob->data;
            $data['department_name'] = $deptName;
            $eligibilityJob->data    = $data;
            $eligibilityJob->save();
        }

        return $eligibilityJob;
    }
}
