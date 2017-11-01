<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 07/01/2017
 * Time: 12:07 AM
 */

namespace App\Contracts\Importer\ImportedMedicalRecord;

/**
 * ImportedSections are CcdaSections that have been imported and stored in
 *
 * Interface ImportedSection
 * @package App\Contracts\CCDA
 */
interface ImportedSection
{
    /**
     * After QA is done, we Import the data.
     *
     * @return bool
     */
    public function createCarePlanSection() : bool;

    /**
     * Get a collection of the ImportedItems.
     * An item can be a Problem, Allergy, Medication and so on.
     *
     * @return ImportedItem[]
     */
    public function getImported() : array;
}
