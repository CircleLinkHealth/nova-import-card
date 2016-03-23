<?php

namespace App\CLH\CCD\Importer\StorageStrategies\Problems;


use App\CarePlan;
use App\CLH\CCD\Importer\StorageStrategies\BaseStorageStrategy;
use App\CLH\Contracts\CCD\StorageStrategy;


class ProblemsToMonitor extends BaseStorageStrategy implements StorageStrategy
{
    public function import($cpmProblemNames = [])
    {
        if ( empty($cpmProblemNames) ) return;

        $carePlan = CarePlan::where( 'program_id', '=', $this->blogId )->where( 'type', '=', 'Program Default' )->first();

        if ( !$carePlan ) {
            throw new \Exception( 'Careplan Not Found' );
        }

        foreach ( $cpmProblemNames as $cpmProblemName ) {
            $carePlan->setCareItemUserValue( $this->user, $cpmProblemName, "Active" );
        }
    }
}