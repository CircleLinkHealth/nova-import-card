<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 07/01/2017
 * Time: 2:51 AM
 */

namespace App\Traits;


use App\Contracts\Importer\ImportedHealthRecord\ImportedHealthRecord;

trait ImportableTrait
{
    /**
     * This handles parsing a resource and storing it for QA.
     * Parsing a resource means it
     *
     * @return ImportedHealthRecord
     *
     */
    public function import() : ImportedHealthRecord
    {
        $this->importAllergies()
            ->importDemographics()
            ->importDocument()
            ->importMedications()
            ->importProblems()
            ->importProvider();
    }
}