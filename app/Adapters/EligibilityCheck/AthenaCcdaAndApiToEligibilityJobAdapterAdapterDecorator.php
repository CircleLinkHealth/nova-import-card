<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Adapters\EligibilityCheck;

use App\Contracts\Importer\MedicalRecord\MedicalRecord;
use App\EligibilityJob;
use App\Importer\Models\ItemLogs\InsuranceLog;
use App\TargetPatient;
use App\ValueObjects\Athena\Insurances;
use Illuminate\Support\Collection;

class AthenaCcdaAndApiToEligibilityJobAdapterAdapterDecorator implements EligibilityCheckAdapter
{
    /**
     * @var EligibilityCheckAdapter
     */
    protected $adapter;
    /**
     * @var TargetPatient
     */
    protected $targetPatient;

    /**
     * @var Collection
     */
    private $insuranceCollection;

    /**
     * AthenaCcdaAndApiToEligibilityJobAdapterAdapterDecorator constructor.
     *
     * @param EligibilityCheckAdapter $adapter
     * @param TargetPatient           $targetPatient
     */
    public function __construct(EligibilityCheckAdapter $adapter, TargetPatient $targetPatient)
    {
        $this->adapter       = $adapter;
        $this->targetPatient = $targetPatient;
    }

    /**
     * @throws \Exception
     *
     * @return EligibilityJob
     */
    public function adaptToEligibilityJob(): EligibilityJob
    {
        return $this->addInsurancesFromAthena($this->adapter->adaptToEligibilityJob());
    }

    /**
     * @return Collection|null
     */
    public function getInsuranceCollection(): ?Collection
    {
        return $this->insuranceCollection;
    }

    /**
     * @return MedicalRecord
     */
    public function getMedicalRecord(): MedicalRecord
    {
        return $this->adapter->getMedicalRecord();
    }

    /**
     * @param $patientId
     * @param $practiceId
     * @param $departmentId
     *
     * @return Insurances
     */
    public function getPatientInsurances($patientId, $practiceId, $departmentId)
    {
        $insurancesResponse = app('athena.api')->getPatientInsurances($patientId, $practiceId, $departmentId);

        $insurances = new Insurances();
        $insurances->setInsurances($insurancesResponse['insurances']);

        return $insurances;
    }

    /**
     * @param EligibilityJob $eligibilityJob
     *
     * @return EligibilityJob
     */
    private function addInsurancesFromAthena(EligibilityJob $eligibilityJob)
    {
        $insurancesLogs = $this->getAndStoreInsuranceFromAthenaApi($this->targetPatient);

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
     * @return Collection
     */
    private function getAndStoreInsuranceFromAthenaApi(TargetPatient $targetPatient)
    {
        $insurances = $this->getPatientInsurances(
            $targetPatient->ehr_patient_id,
            $targetPatient->ehr_practice_id,
            $targetPatient->ehr_department_id
        );

        return $this->storeInsuranceFromAthenaApi($insurances, $this->adapter->getMedicalRecord());
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
