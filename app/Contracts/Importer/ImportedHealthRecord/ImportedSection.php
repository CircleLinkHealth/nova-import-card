<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 07/01/2017
 * Time: 12:07 AM
 */

namespace App\Contracts\Importer\ImportedHealthRecord;


/**
 * Parsed Sections are CcdaSections
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
}