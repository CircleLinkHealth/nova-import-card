<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer\Loggers\Ccda;

use App\Search\ProviderByName;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\MedicalRecordLogger;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\DemographicsLog;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\DocumentLog;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\InsuranceLog;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\MedicationLog;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProblemCodeLog;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProblemLog;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProviderLog;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Loggers\CcdToLogTranformer;
use CircleLinkHealth\SharedModels\Entities\AllergyLog;
use CircleLinkHealth\SharedModels\Entities\Ccda;

class CcdaSectionsLogger implements MedicalRecordLogger
{
    protected $ccd;
    protected $ccdaId;
    
    protected $foreignKeys = [];
    
    protected $transformer;
    protected $vendorId;
    protected   $problemLogs;
    /**
     * @var Ccda
     */
    protected $mr;
    
    public function __construct(Ccda $ccd)
    {
        $this->ccd = $ccd->bluebuttonJson();
        $this->mr = $ccd;
        
        $this->ccdaId   = $ccd->id;
        $this->vendorId = $ccd->vendor_id;
        
        $this->foreignKeys = [
            'vendor_id'           => $this->vendorId,
            'medical_record_type' => Ccda::class,
            'medical_record_id'   => $this->ccdaId,
        ];
        
        $this->transformer = new CcdToLogTranformer();
    }
    
    /**
     * Transform the Allergies Section into Log models..
     */
    public function logAllergiesSection(): MedicalRecordLogger
    {
        $allergies = $this->ccd->allergies;
        
        foreach ($allergies as $allergy) {
            $saved = AllergyLog::updateOrCreate(
                array_merge($this->transformer->allergy($allergy), $this->foreignKeys)
            );
        }
        
        return $this;
    }
    
    /**
     * Log all Sections.
     */
    public function logAllSections()
    {
        $this->logAllergiesSection()
             ->logDemographicsSection()
             ->logDocumentSection()
             ->logInsuranceSection()
             ->logMedicationsSection()
             ->logProblemsSection()
             ->logProvidersSection();
    }
    
    /**
     * Transform the Demographics Section into Log models..
     */
    public function logDemographicsSection(): MedicalRecordLogger
    {
        $demographics = $this->ccd->demographics;
        
        $saved = DemographicsLog::updateOrCreate(
            array_merge($this->transformer->demographics($demographics), $this->foreignKeys)
        );
        
        return $this;
    }
    
    /**
     * Transform the Document Section into Log models..
     */
    public function logDocumentSection(): MedicalRecordLogger
    {
        $document = $this->ccd->document;
        
        $data = $this->transformer->document($document);
        
        $shouldBeIgnored = DocumentLog::where('ml_ignore', '=', 1)
                                      ->where('custodian', $data['custodian'])
                                      ->first();
        
        if ($shouldBeIgnored) {
            return $this;
        }
        
        $saved = DocumentLog::updateOrCreate(
            array_merge($data, $this->foreignKeys)
        );
        
        return $this;
    }
    
    /**
     * Log Insurance Section.
     */
    public function logInsuranceSection(): MedicalRecordLogger
    {
        if ( ! empty($this->ccd->payers)) {
            foreach ($this->ccd->payers as $payer) {
                if (empty($payer->insurance)) {
                    continue;
                }
                
                $insurance = InsuranceLog::updateOrCreate(
                    array_merge(
                        $this->transformer->insurance($payer),
                        [
                            'medical_record_id'   => $this->ccdaId,
                            'medical_record_type' => Ccda::class,
                            'import'              => true,
                        ]
                    )
                );
            }
        }
        
        return $this;
    }
    
    /**
     * Transform the Medications Section into Log models..
     */
    public function logMedicationsSection(): MedicalRecordLogger
    {
        $medications = $this->ccd->medications;
        
        foreach ($medications as $med) {
            $saved = MedicationLog::updateOrCreate(
                array_merge($this->transformer->medication($med), $this->foreignKeys)
            );
        }
        
        return $this;
    }
    
    /**
     * Transform the Problems Section into Log models..
     */
    public function logProblemsSection(): MedicalRecordLogger
    {
        $problems = $this->ccd->problems;
        
        foreach ($problems as $prob) {
            $problemLog = ProblemLog::updateOrCreate(
                array_merge($this->transformer->problem($prob), $this->foreignKeys)
            );
            
            $codes = $this->transformer->problemCodes($prob);
            
            foreach ($codes as $code) {
                $code['ccd_problem_log_id'] = $problemLog->id;
                
                if ( ! $code['code']) {
                    continue;
                }
                ProblemCodeLog::updateOrCreate($code);
            }
            
            $this->problemLogs[] = $problemLog;
        }
        
        return $this;
    }
    
    /**
     * Transform the Providers Section into Log models..
     */
    public function logProvidersSection(): MedicalRecordLogger
    {
        $providers = $this->transformer->parseProviders($this->ccd->document, $this->ccd->demographics);
        
        foreach ($providers as $provider) {
            $data = $this->transformer->provider($provider);
            
            $shouldBeIgnored = ProviderLog::where('ml_ignore', '=', 1)
                                          ->where('first_name', $data['first_name'])
                                          ->where('last_name', $data['last_name'])
                                          ->where('street', $data['street'])
                                          ->where('city', $data['city'])
                                          ->where('state', $data['state'])
                                          ->where('zip', $data['zip'])
                                          ->where('cell_phone', $data['cell_phone'])
                                          ->where('home_phone', $data['home_phone'])
                                          ->where('work_phone', $data['work_phone'])
                                          ->exists();
            
            if ($shouldBeIgnored || empty(array_filter($data))) {
                continue;
            }
            
            $searchProvider = ProviderByName::first("{$data['first_name']} {$data['last_name']}");
            
            $data = [
                'billing_provider_id',
                'provider_id',
                'practice_id',
                'location_id',
            ];
            
            if ($searchProvider) {
                $data['provider_id'] = $data['billing_provider_id'] = $searchProvider->id;
                $data['practice_id'] = $searchProvider->program_id;
                $data['location_id'] = optional($searchProvider->loadMissing('locations')->locations->first())->id;
            }
            
            if ( ! $data['practice_id']) {
                $data['practice_id'] = $this->mr->practice_id;
            }
            
            if ( ! $data['location_id']) {
                $data['location_id'] = $this->mr->location_id;
            }
            
            if ( ! $data['billing_provider_id']) {
                $data['provider_id'] = $data['billing_provider_id'] = $this->mr->billing_provider_id;
            }
            
            if ($data['practice_id']) {
                $saved = ProviderLog::updateOrCreate(
                    array_merge(
                        [
                            'first_name' => $data['first_name'],
                            'last_name'  => $data['last_name'],
                        ],
                        $this->foreignKeys
                    ),
                    array_merge($data, $this->foreignKeys)
                );
            }
        }
        
        return $this;
    }
}
