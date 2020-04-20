<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecord\Templates;

interface MedicalRecordTemplate
{
    public function fillAllergiesSection(): array;

    public function fillDemographicsSection(): object;

    public function fillDocumentSection(): object;

    public function fillMedicationsSection(): array;

    public function fillPayersSection(): array;

    public function fillProblemsSection(): array;

    public function fillVitals(): array;

    public function getAllergies();

    public function getDemographics();

    public function getDocument();

    public function getMedications();

    public function getPayers();

    public function getProblems();

    public function getType(): string;

    public function getVitals();

    public function toArray(): array;

    public function toJson(): string;
}
