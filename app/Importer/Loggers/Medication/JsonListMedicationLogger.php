<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 3/12/18
 * Time: 6:26 PM
 */

namespace App\Importer\Loggers\Medication;


use App\Contracts\Importer\MedicalRecord\Section\Logger;

class JsonListMedicationLogger implements Logger
{

    public function handle($medicalRecord): array
    {
        // TODO: Implement handle() method.
    }

    public function shouldHandle($medicalRecord): bool
    {
        // TODO: Implement shouldHandle() method.
    }
}