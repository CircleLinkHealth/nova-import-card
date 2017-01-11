<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 07/01/2017
 * Time: 2:51 AM
 */

namespace App\Traits;


use App\Contracts\Importer\ImportedMedicalRecord\ImportedMedicalRecord;

trait ImportableTrait
{
    /**
     * This handles parsing a resource and storing it for QA.
     * Parsing a resource means it
     *
     * @return ImportedMedicalRecord
     *
     */
    public function import() : ImportedMedicalRecord
    {
        $this->importAllergies()
            ->importDemographics()
            ->importDocument()
            ->importMedications()
            ->importProblems()
            ->importProvider();
    }
}