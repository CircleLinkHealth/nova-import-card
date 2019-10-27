<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Decorators;

use App\Contracts\Importer\MedicalRecord\MedicalRecord;
use App\EligibilityJob;
use App\Importer\Models\ItemLogs\InsuranceLog;
use App\Models\MedicalRecords\Ccda;
use App\TargetPatient;
use App\ValueObjects\Athena\Insurances;
use CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation;
use Illuminate\Support\Collection;

class AddInsuranceFromAthenaToEligibilityJob
{
    /**
     * @var AthenaApiImplementation
     */
    protected $athenaApiImplementation;
    /**
     * @var Collection
     */
    private $insuranceCollection;

    public function __construct(AthenaApiImplementation $athenaApiImplementation)
    {
        $this->athenaApiImplementation = $athenaApiImplementation;
    }

    /**
     * @param EligibilityJob $eligibilityJob
     * @param TargetPatient  $targetPatient
     * @param Ccda           $ccda
     *
     * @throws \Exception
     *
     * @return EligibilityJob
     */
    public function addInsurancesFromAthena(EligibilityJob $eligibilityJob, TargetPatient $targetPatient, Ccda $ccda)
    {
        $response = $this->getAndStoreInsuranceFromAthenaApi($targetPatient, $ccda);

        if (is_array($response) && array_key_exists('insurances', $response)) {
            $data                 = $eligibilityJob->data;
            $data['insurances']   = $response['insurances'];
            $eligibilityJob->data = $data;
        }

        $eligibilityJob->save();

        return $eligibilityJob;
    }

    /**
     * @param TargetPatient $targetPatient
     *
     * @throws \Exception
     *
     * @return Insurances
     */
    private function getAndStoreInsuranceFromAthenaApi(TargetPatient $targetPatient, Ccda $ccda)
    {
        $insurances = $this->athenaApiImplementation->getPatientInsurances(
            $targetPatient->ehr_patient_id,
            $targetPatient->ehr_practice_id,
            $targetPatient->ehr_department_id
        );

        return tap($insurances, function (array $insurances) use ($ccda) {
            $this->storeInsuranceFromAthenaApi($insurances, $ccda);
        });
    }

    /**
     * @param array         $insurances
     * @param MedicalRecord $medicalRecord
     *
     * @return Collection
     */
    private function storeInsuranceFromAthenaApi(array $insurances, MedicalRecord $medicalRecord)
    {
        $this->insuranceCollection = collect();

        if (array_key_exists('insurances', $insurances)) {
            foreach ($insurances['insurances'] as $insurance) {
                $this->insuranceCollection->push(InsuranceLog::create([
                    'medical_record_id'   => optional($medicalRecord)->id,
                    'medical_record_type' => get_class($medicalRecord),
                    'name'                => $insurance['insuranceplanname'] ?? null,
                    'type'                => $insurance['insurancetype'] ?? null,
                    'policy_id'           => $insurance['policynumber'] ?? null,
                    'relation'            => $insurance['relationshiptoinsured'] ?? null,
                    'subscriber'          => $insurance['insurancepolicyholder'] ?? null,
                    'import'              => 1,
                    'raw'                 => $insurance,
                ]));
            }
        }

        return $this->insuranceCollection;
    }
}
