<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 3/12/18
 * Time: 6:28 PM
 */

namespace App\Importer\Loggers\Problem;


use App\Contracts\Importer\MedicalRecord\Section\Logger;

class JsonListProblemLogger implements Logger
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