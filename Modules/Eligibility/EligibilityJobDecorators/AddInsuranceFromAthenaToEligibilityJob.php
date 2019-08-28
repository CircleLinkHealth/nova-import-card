<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\EligibilityJobDecorators;

use App\Contracts\Importer\MedicalRecord\MedicalRecord;
use App\EligibilityJob;
use App\Importer\Models\ItemLogs\InsuranceLog;
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
     * @var
     */
    private $insuranceCollection;

    public function __construct(AthenaApiImplementation $athenaApiImplementation)
    {
        $this->athenaApiImplementation = $athenaApiImplementation;
    }

    /**
     * @param EligibilityJob $eligibilityJob
     * @param TargetPatient  $targetPatient
     *
     * @return EligibilityJob
     */
    public function addInsurancesFromAthena(EligibilityJob $eligibilityJob, TargetPatient $targetPatient)
    {
        $insurancesLogs = $this->getAndStoreInsuranceFromAthenaApi($targetPatient);

        $insurances = new Insurances();
        $insurances->setInsurances($insurancesLogs);

        $data               = $eligibilityJob->data;
        $data['insurances'] = $insurances->getInsurancesForEligibilityCheck();

        $eligibilityJob->data = $data;
        $eligibilityJob->save();

        return $eligibilityJob;
    }

    /**
     * @param TargetPatient $targetPatient
     *
     * @throws \Exception
     *
     * @return Collection
     */
    private function getAndStoreInsuranceFromAthenaApi(TargetPatient $targetPatient)
    {
        $insurances = $this->getPatientInsurances(
            $targetPatient->ehr_patient_id,
            $targetPatient->ehr_practice_id,
            $targetPatient->ehr_department_id
        );

        return $this->storeInsuranceFromAthenaApi($insurances, $targetPatient->ccda);
    }

    /**
     * @param $patientId
     * @param $practiceId
     * @param $departmentId
     *
     * @throws \Exception
     *
     * @return Insurances
     */
    private function getPatientInsurances($patientId, $practiceId, $departmentId)
    {
        $insurancesResponse = $this->athenaApiImplementation->getPatientInsurances($patientId, $practiceId, $departmentId);

        $insurances = new Insurances();
        $insurances->setInsurances($insurancesResponse['insurances']);

        return $insurances;
    }

    /**
     * @param Insurances    $insurances
     * @param MedicalRecord $medicalRecord
     *
     * @return Collection
     */
    private function storeInsuranceFromAthenaApi(Insurances $insurances, MedicalRecord $medicalRecord)
    {
        $this->insuranceCollection = collect();

        foreach ($insurances->getInsurances() as $insurance) {
            $this->insuranceCollection->push(InsuranceLog::create([
                'medical_record_id'   => $medicalRecord->getId(),
                'medical_record_type' => $medicalRecord->getType(),
                'name'                => $insurance['insuranceplanname'],
                'type'                => $insurance['insurancetype'],
                'policy_id'           => $insurance['policynumber'],
                'relation'            => $insurance['relationshiptoinsured'],
                'subscriber'          => $insurance['insurancepolicyholder'],
                'import'              => 1,
                'raw'                 => $insurance,
            ]));
        }

        return $this->insuranceCollection;
    }
}
