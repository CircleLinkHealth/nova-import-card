<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts;

use App\Contracts\Importer\ImportedMedicalRecord\ImportedItem;

/**
 * ImportedSections are CcdaSections that have been imported and stored in.
 *
 * Interface ImportedSection
 */
interface ImportedSection
{
    /**
     * After QA is done, we Import the data.
     */
    public function createCarePlanSection(): bool;

    /**
     * Get a collection of the ImportedItems.
     * An item can be a Problem, Allergy, Medication and so on.
     *
     * @return ImportedItem[]
     */
    public function getImported(): array;
}
