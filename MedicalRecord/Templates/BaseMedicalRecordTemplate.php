<?php


namespace CircleLinkHealth\Eligibility\MedicalRecord\Templates;


abstract class BaseMedicalRecordTemplate implements MedicalRecordTemplate
{
    private $document;
    private $type;
    private $problems;
    private $medications;
    private $demographics;
    private $vitals;
    private $payers;
    private $allergies;
    
    public function toArray(): array
    {
        return [
            'type'         => $this->getType(),
            'document'     => $this->getDocument(),
            'allergies'    => $this->getAllergies(),
            'demographics' => $this->getDemographics(),
            'medications'  => $this->getMedications(),
            'payers'       => $this->getPayers(),
            'problems'     => $this->getProblems(),
            'vitals'       => $this->getVitals(),
        ];
    }
    
    /**
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
    
    public function getType()
    {
        if ( ! $this->type) {
            $this->type = __CLASS__;
        }
        
        return $this->type;
    }
    
    public function getProblems()
    {
        if ( ! $this->problems) {
            $this->problems = $this->fillProblemsSection();
        }
        
        return $this->problems;
    }
    
    public function getMedications()
    {
        if ( ! $this->medications) {
            $this->medications = $this->fillMedicationsSection();
        }
        
        return $this->medications;
    }
    
    public function getDemographics()
    {
        if ( ! $this->demographics) {
            $this->demographics = $this->fillDemographicsSection();
        }
        
        return $this->demographics;
    }
    
    public function getVitals()
    {
        if ( ! $this->vitals) {
            $this->vitals = $this->fillVitals();
        }
        
        return $this->vitals;
    }
    
    public function getPayers()
    {
        if ( ! $this->payers) {
            $this->payers = $this->fillPayersSection();
        }
        
        return $this->payers;
    }
    
    public function getAllergies()
    {
        if ( ! $this->allergies) {
            $this->allergies = $this->fillAllergiesSection();
        }
        
        return $this->allergies;
    }
    
    public function getDocument()
    {
        if ( ! $this->document) {
            $this->document = $this->fillDocumentSection();
        }
        
        return $this->document;
    }
}