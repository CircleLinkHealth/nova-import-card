<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 3/12/18
 * Time: 2:14 PM
 */

namespace App\Importer\Loggers\Allergy;


interface AllergyLogger
{
    public function handle($medicalRecord) : array;

    public function shouldHandle($medicalRecord) : bool;
}