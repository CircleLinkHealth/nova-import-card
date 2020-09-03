<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Customer;


use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary;
use Illuminate\Database\Eloquent\Collection;

class RenewLocationSummaries
{
    public static function execute(int $locationId, Carbon $renewForMonth){
        //todo:fillout
    }
    public static function fromSummariesCollection(Collection $pastSummaries, Carbon $renewForMonth)
    {
        $pastSummaries->each(function (ChargeableLocationMonthlySummary $clms) use ($renewForMonth) {
            $this->repo()->store($clms->location_id, $clms->chargeable_service_id, $renewForMonth, $clms->amount);
        });
    }
}