<?php

namespace App\CLH\CCD\Importer\StorageStrategies\Medications;


use App\CarePlan;
use App\CLH\CCD\Importer\StorageStrategies\BaseStorageStrategy;
use App\CLH\Contracts\CCD\StorageStrategy;
use App\CPRulesItem;
use App\CPRulesPCP;
use App\CPRulesUCP;
use Illuminate\Support\Facades\Log;

class MedicationsList extends BaseStorageStrategy implements StorageStrategy
{

    public function import($medsList)
    {
        $carePlan = CarePlan::where( 'program_id', '=', $this->blogId )->where( 'type', '=', 'Program Default' )->first();

        if ( !$carePlan ) {
            throw new \Exception( 'Unable to build careplan' );
        }

        $carePlan->setCareItemUserValue( $this->user, 'medication-list-details', $medsList );
        $carePlan->setCareItemUserValue( $this->user, 'cf-sol-med-ohm-medication-list', 'Active' );
    }
}