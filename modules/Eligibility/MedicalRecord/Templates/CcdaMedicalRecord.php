<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecord\Templates;

class CcdaMedicalRecord extends BaseMedicalRecordTemplate
{
    /**
     * @var array
     */
    protected $ccda;

    /**
     * CcdaMedicalRecord constructor.
     */
    public function __construct(object $ccda)
    {
        $this->ccda = $ccda;
    }

    public function fillAllergiesSection(): array
    {
        return (array) $this->ccda->allergies;
    }

    public function fillDemographicsSection(): array
    {
        return $this->ccda->demographics;
    }

    public function fillDocumentSection(): array
    {
        return $this->ccda->document;
    }

    public function fillEncountersSection(): array
    {
        return [];
    }

    public function fillMedicationsSection(): array
    {
        return (array) $this->ccda->medications;
    }

    public function fillPayersSection(): array
    {
        return (array) $this->ccda->payers;
    }

    public function fillProblemsSection(): array
    {
        return (array) $this->ccda->problems;
    }

    public function fillVitals(): array
    {
        return (array) $this->ccda->vitals;
    }

    public function getAllergies()
    {
        return $this->fillAllergiesSection();
    }

    public function getDemographics()
    {
        return $this->fillDemographicsSection();
    }

    public function getDocument()
    {
        return $this->fillDocumentSection();
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

    public function getType(): string
    {
        return $this->ccda->type;
    }

    public function getVitals()
    {
        return $this->fillVitals();
    }
}
