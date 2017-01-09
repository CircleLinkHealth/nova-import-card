<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 07/01/2017
 * Time: 1:31 AM
 */

namespace App\Contracts\Importer\HealthRecord\Section;

/**
 * This is a Section Importer. It allows for each Health Section to be able to be imported into CPM.
 *
 * Interface Importer
 * @package App\Contracts\Importer\HealthRecord\Section
 */
interface Importer
{
    public function import();

    public function getValidator();

    public function validate();
}