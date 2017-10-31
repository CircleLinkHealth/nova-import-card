<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 07/01/2017
 * Time: 1:35 AM
 */

namespace App\Contracts\Importer\MedicalRecord\Section;

/**
 * This is a Section Validator. We use it to decide whether the data is Valid and should be imported.
 *
 * Interface Validator
 * @package App\Contracts\Importer\MedicalRecord\Section
 */
interface Validator
{
    public function shouldValidate(ItemLog $item) : bool;

    public function isValid(ItemLog $item) : bool;
}
