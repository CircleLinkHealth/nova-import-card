<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\CLH\CCD\Importer\ParsingStrategies\Location;

use App\CLH\Contracts\CCD\ParsingStrategy;
use App\CLH\Contracts\CCD\ValidationStrategy;
use App\Importer\Models\ItemLogs\ProviderLog;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\SharedModels\Entities\Ccda;

class ProviderLocation implements ParsingStrategy
{
    public function parse(Ccda $ccd, ValidationStrategy $validator = null)
    {
        $providers = ProviderLog::whereCcdaId($ccd->id)->get();

        $locations = [];
        foreach ($providers as $provider) {
            $loc = Location::where('address_line_1', $provider->street)
                ->whereIn('phone', [$provider->cell_phone, $provider->home_phone, $provider->work_phone])
                ->get();

            if (count($loc) > 0) {
                foreach ($loc as $l) {
                    array_push($locations, $l);
                    $provider->import = true;
                    $provider->save();
                }
            }
        }

        if (count($locations) > 0) {
            return $locations;
        }

        return false;
    }
}
