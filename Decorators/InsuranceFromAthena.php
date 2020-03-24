<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Decorators;

use CircleLinkHealth\Eligibility\Contracts\MedicalRecordDecorator;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\MedicalRecord;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\InsuranceLog;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use CircleLinkHealth\Eligibility\Entities\TargetPatient;
use App\ValueObjects\Athena\Insurances;
use CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation;
use Illuminate\Support\Collection;

class InsuranceFromAthena implements MedicalRecordDecorator
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
     * @param TargetPatient $targetPatient
     * @param Ccda $ccda
     *
     * @return EligibilityJob
     * @throws \Exception
     */
    public function decorate(EligibilityJob $eligibilityJob):EligibilityJob
    {
        $eligibilityJob->loadMissing('targetPatient.ccda');
    
        if (array_key_exists('insurances', $eligibilityJob->data) && ! empty($eligibilityJob->data['insurances'])) {
            return $this->addInsuranceFromEligibilityJob($eligibilityJob);
        }
        
        return $this->addInsuranceFromAthenaApi($eligibilityJob);
    }
    
    /**
     * @param TargetPatient $targetPatient
     * @param Ccda $ccda
     *
     * @return Insurances
     * @throws \Exception
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
                $this->storeInsuranceLogsFromAthenaApi($insurances, $ccda);
            }
        );
    }
    
    /**
     * @return Collection
     */
    private function storeInsuranceLogsFromAthenaApi(array $insurances, MedicalRecord $medicalRecord)
    {
        $this->insuranceCollection = collect();
        
        if (array_key_exists('insurances', $insurances)) {
            foreach ($insurances['insurances'] as $insurance) {
                $this->insuranceCollection->push(
                    InsuranceLog::updateOrCreate(
                        [
                            'medical_record_id'   => optional($medicalRecord)->id,
                            'medical_record_type' => get_class($medicalRecord),
                            'name'                => $insurance['insuranceplanname'] ?? null,
                            'type'                => $insurance['insurancetype'] ?? null,
                            'policy_id'           => $insurance['policynumber'] ?? null,
                            'relation'            => $insurance['relationshiptoinsured'] ?? null,
                            'subscriber'          => $insurance['insurancepolicyholder'] ?? null,
                            'import'              => 1,
                            'raw'                 => $insurance,
                        ]
                    )
                );
            }
        }
        
        return $this->insuranceCollection;
    }
    
    /**
     * @param EligibilityJob $eligibilityJob
     * @param array $record
     */
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
     * @param EligibilityJob $eligibilityJob
     *
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
    
    /**
     * @param EligibilityJob $eligibilityJob
     *
     * @return EligibilityJob
     * @throws \Exception
     */
    private function addInsuranceFromAthenaApi(EligibilityJob &$eligibilityJob)
    {
        $response = $this->getAndStoreInsuranceLogsFromAthenaApi($eligibilityJob->targetPatient, $eligibilityJob->targetPatient->ccda);
    
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
}
