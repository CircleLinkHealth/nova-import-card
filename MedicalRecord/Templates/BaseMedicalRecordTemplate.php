<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecord\Templates;

abstract class BaseMedicalRecordTemplate implements MedicalRecordTemplate
{
    private $allergies;
    private $demographics;
    private $document;
    private $medications;
    private $payers;
    private $problems;
    private $type;
    private $vitals;

    public function getAllergies()
    {
        if ( ! $this->allergies) {
            $this->allergies = $this->fillAllergiesSection();
        }

        return $this->allergies;
    }

    public function getDemographics()
    {
        if ( ! $this->demographics) {
            $this->demographics = $this->fillDemographicsSection();
        }

        return $this->demographics;
    }

    public function getDocument()
    {
        if ( ! $this->document) {
            $this->document = $this->fillDocumentSection();
        }

        return $this->document;
    }

    public function getMedications()
    {
        if ( ! $this->medications) {
            $this->medications = $this->fillMedicationsSection();
        }

        return $this->medications;
    }

    public function getPayers()
    {
        if ( ! $this->payers) {
            $this->payers = $this->fillPayersSection();
        }

        return $this->payers;
    }

    public function getProblems()
    {
        if ( ! $this->problems) {
            $this->problems = $this->fillProblemsSection();
        }

        return $this->problems;
    }

    abstract public function getType(): string;

    public function getVitals()
    {
        if ( ! $this->vitals) {
            $this->vitals = $this->fillVitals();
        }

        return $this->vitals;
    }

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

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}
