<?php

namespace CircleLinkHealth\Eligibility\MedicalRecord\Templates;

class CcdaMedicalRecord extends BaseMedicalRecordTemplate
{
    /**
     * @var array
     */
    protected $ccda;
    
    /**
     * CcdaMedicalRecord constructor.
     *
     * @param object $ccda
     */
    public function __construct(object $ccda)
    {
        $this->ccda = $ccda;
    }
    
    public function getDocument()
    {
        return $this->fillDocumentSection();
    }
    
    public function getAllergies()
    {
        return $this->fillAllergiesSection();
    }
    
    public function getDemographics()
    {
        return $this->fillDemographicsSection();
    }
    
    public function getMedications()
    {
        return $this->fillMedicationsSection();
    }
    
    public function getPayers()
    {
        return $this->fillPayersSection();
    }
    
    public function getProblems()
    {
        return $this->fillProblemsSection();
    }
    
    public function getVitals()
    {
        return $this->fillVitals();
    }
    
    public function fillAllergiesSection(): array
    {
        return (array) $this->ccda->allergies;
    }
    
    public function fillDemographicsSection(): object
    {
        return $this->ccda->demographics;
    }
    
    public function fillDocumentSection(): object
    {
        return $this->ccda->document;
    }
    
    public function fillMedicationsSection(): array
    {
        return (array) $this->ccda->medications;
    }
    
    public function fillProblemsSection(): array
    {
        return (array) $this->ccda->problems;
    }
    
    public function fillVitals(): array
    {
        return (array) $this->ccda->vitals;
    }
    
    public function fillPayersSection(): array
    {
        return (array) $this->ccda->payers;
    }
    
    public function getType(): string
    {
        return $this->ccda->type;
    }
}