<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Decorators;

use App\Traits\ValidatesDates;
use CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation;
use CircleLinkHealth\Eligibility\Contracts\MedicalRecordDecorator;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use Illuminate\Support\Facades\DB;

class MedicalHistoryFromAthena implements MedicalRecordDecorator
{
    use ValidatesDates;

    /**
     * @var AthenaApiImplementation
     */
    protected $api;

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

        $data = $eligibilityJob->data;
        if ( ! array_key_exists('medical_history', $data)) {
            $data['medical_history'] = $this->api->getMedicalHistory(
                $eligibilityJob->targetPatient->ehr_patient_id,
                $eligibilityJob->targetPatient->ehr_practice_id,
                $eligibilityJob->targetPatient->ehr_department_id
            );
        }
        collect($data['medical_history']['questions'])->where(
            'answer',
            'Y'
        )->pluck(
            'question'
        )->unique()->each(
            function ($problemName) use (&$data) {
                $data['problems'][] = [
                    'name' => $problemName,
                ];
            }
        );
        $eligibilityJob->data = $data;

        if ($eligibilityJob->isDirty()) {
            $eligibilityJob->save();
            DB::commit();
        }

        return $eligibilityJob;
    }
}
