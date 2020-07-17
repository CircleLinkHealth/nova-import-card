<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Decorators;

use App\ValueObjects\Athena\Insurances;
use CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation;
use CircleLinkHealth\Eligibility\Contracts\MedicalRecordDecorator;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\Eligibility\Entities\TargetPatient;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Support\Collection;

class InsuranceFromAthena implements MedicalRecordDecorator
{
    /**
     * @var AthenaApiImplementation
     */
    protected $athenaApiImplementation;

    public function __construct(AthenaApiImplementation $athenaApiImplementation)
    {
        $this->athenaApiImplementation = $athenaApiImplementation;
    }

    /**
     * @return Collection
     */
    public function addInsurancesToCcda(array $insurances, Ccda $ccda)
    {
        if (array_key_exists('insurances', $insurances)) {
            $json = $ccda->bluebuttonJson();
            foreach ($insurances['insurances'] as $insurance) {
                array_push(
                    $json->payers,
                    [
                        'insurance'   => $insurance['insuranceplanname'] ?? null,
                        'policy_type' => $insurance['insurancetype'] ?? null,
                        'policy_id'   => $insurance['policynumber'] ?? null,
                        'relation'    => $insurance['relationshiptoinsured'] ?? null,
                        'subscriber'  => $insurance['insurancepolicyholder'] ?? null,
                    ]
                );
            }
            $ccda->json = json_encode($json);
            $ccda->save();
        }
    }

    /**
     * @param TargetPatient $targetPatient
     * @param Ccda          $ccda
     *
     * @throws \Exception
     */
    public function decorate(EligibilityJob $eligibilityJob): EligibilityJob
    {
        $eligibilityJob->loadMissing('targetPatient.ccda');

        if (array_key_exists('insurances', $eligibilityJob->data) && ! empty($eligibilityJob->data['insurances'])) {
            return $this->addInsuranceFromEligibilityJob($eligibilityJob);
        }

        if ($eligibilityJob->targetPatient && $eligibilityJob->targetPatient->ccda) {
            return $this->addInsuranceFromAthenaApi($eligibilityJob);
        }

        return $eligibilityJob;
    }

    /**
     * @throws \Exception
     *
     * @return EligibilityJob
     */
    private function addInsuranceFromAthenaApi(EligibilityJob &$eligibilityJob)
    {
        $response = $this->getAndStoreInsuranceLogsFromAthenaApi(
            $eligibilityJob->targetPatient,
            $eligibilityJob->targetPatient->ccda
        );

        if (is_array($response) && array_key_exists('insurances', $response)) {
            $data               = $eligibilityJob->data;
            $data['insurances'] = $response['insurances'];

            $this->fillInsuranceFields($eligibilityJob, $data);

            $eligibilityJob->data = $data;
        }

        if ($eligibilityJob->isDirty()) {
            $eligibilityJob->save();
        }

        return $eligibilityJob;
    }

    /**
     * @return EligibilityJob
     */
    private function addInsuranceFromEligibilityJob(EligibilityJob &$eligibilityJob)
    {
        if (empty($eligibilityJob->primary_insurance) || empty($eligibilityJob->secondary_insurance) || empty($eligibilityJob->tertiary_insurance)) {
            $data = $eligibilityJob->data;
            $this->fillInsuranceFields($eligibilityJob, $data);
            $eligibilityJob->data = $data;
            $eligibilityJob->save();
        }

        return $eligibilityJob;
    }

    private function fillInsuranceFields(EligibilityJob &$eligibilityJob, array &$record)
    {
        $i = 0;

        foreach ($record['insurances'] as $insurance) {
            if (array_key_exists('insuranceplanname', $insurance)) {
                if (0 == $i) {
                    $eligibilityJob->primary_insurance = $record['primary_insurance'] = $insurance['insuranceplanname'];
                    ++$i;
                } elseif (1 == $i) {
                    $eligibilityJob->secondary_insurance = $record['secondary_insurance'] = $insurance['insuranceplanname'];
                    ++$i;
                } elseif (2 == $i) {
                    $eligibilityJob->tertiary_insurance = $record['tertiary_insurance'] = $insurance['insuranceplanname'];
                    ++$i;
                }
            }
        }
    }

    /**
     * @throws \Exception
     *
     * @return Insurances
     */
    private function getAndStoreInsuranceLogsFromAthenaApi(TargetPatient $targetPatient, Ccda $ccda)
    {
        $insurances = $this->athenaApiImplementation->getPatientInsurances(
            $targetPatient->ehr_patient_id,
            $targetPatient->ehr_practice_id,
            $targetPatient->ehr_department_id
        );

        return tap(
            $insurances,
            function (array $insurances) use ($ccda) {
                $this->addInsurancesToCcda($insurances, $ccda);
            }
        );
    }
}
