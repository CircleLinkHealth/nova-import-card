<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 4/25/16
 * Time: 3:33 PM
 */

namespace App\Contracts\Repositories;

interface AprimaCcdApiRepository
{
    public function getPatientAndProviderIdsByLocationAndForeignSystem($locationId, $foreignSystem);
}
