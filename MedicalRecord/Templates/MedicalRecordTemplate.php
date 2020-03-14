<?php

namespace CircleLinkHealth\Eligibility\MedicalRecord\Templates;

interface MedicalRecordTemplate
{
    public function toArray(): array;
    
    /**
     * @return string
     */
    public function toJson(): string;
    
    public function fillAllergiesSection(): array;
    
    public function fillDemographicsSection(): object;
    
    public function fillDocumentSection(): object;
    
    public function fillMedicationsSection(): array;
    
    public function fillProblemsSection(): array;
    
    public function fillVitals(): array;
    
    public function fillPayersSection(): array;
    
    public function getProblems();
    
    public function getMedications();
    
    public function getDemographics();
    
    public function getType(): string;
    
    public function getVitals();
    
    public function getPayers();
    
    public function getAllergies();
    
    public function getDocument();
}