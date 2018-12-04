<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 3/12/18
 * Time: 2:14 PM
 */

namespace App\Contracts\Importer\MedicalRecord\Section;

interface Logger
{
    public function handle($problemsString): array;

    public function shouldHandle($problemsString);
}
