<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Customer;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\LocationProcessorRepository;
use CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary;
use Illuminate\Database\Eloquent\Collection;

class RenewLocationSummaries
{
    protected LocationProcessorRepository $repo;

    public static function execute(int $locationId, Carbon $renewForMonth)
    {
        $self = new static();

        $self->renew($self->repo()->pastLocationSummaries($locationId, $renewForMonth), $renewForMonth);
    }

    public static function fromSummariesCollection(Collection $pastSummaries, Carbon $renewForMonth)
    {
        (new static())->renew($pastSummaries, $renewForMonth);
    }

    public function renew(Collection $pastSummaries, Carbon $renewForMonth)
    {
        $pastSummaries->each(function (ChargeableLocationMonthlySummary $clms) use ($renewForMonth) {
            $this->repo()->storeUsingServiceId($clms->location_id, $clms->chargeable_service_id, $renewForMonth, $clms->amount);
        });
    }

    private function repo()
    {
        if ( ! isset($this->repo)) {
            $this->repo = app(LocationProcessorRepository::class);
        }

        return $this->repo;
    }
}