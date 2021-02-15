<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Http\Resources;

use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlyTime;
use CircleLinkHealth\Customer\Entities\ChargeableService as ChargeableServiceModel;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class ChargeableServiceForAbp extends JsonResource
{
    public static function collectionFromChargeableMonthlySummaries(User $user): AnonymousResourceCollection
    {
        $services = $user->chargeableMonthlySummaries
            ->map(function (ChargeablePatientMonthlySummary $item) use ($user) {
                /** @var ChargeablePatientMonthlyTime $time */
                $time = $user->chargeableMonthlyTime->firstWhere('chargeable_service_id', $item->chargeable_service_id);

                return [
                    'id'           => $item->chargeable_service_id,
                    'is_fulfilled' => $item->is_fulfilled,
                    'total_time'   => optional($time)->total_time ?? 0,
                ];
            })
            ->values();

        $user->chargeableMonthlyTime->each(function (ChargeablePatientMonthlyTime $time) use ($services) {
            $entry = $services->firstWhere('chargeable_service_id', $time->chargeable_service_id);
            if ( ! $entry) {
                $services->push([
                    'id'           => $time->chargeable_service_id,
                    'is_fulfilled' => false,
                    'total_time'   => $time->total_time,
                ]);
            }
        });

        return self::collection($services);
    }

    public static function collectionFromPms(PatientMonthlySummary $pms): AnonymousResourceCollection
    {
        /** @var ChargeableServiceModel[]|Collection $cs */
        $cs = $pms->chargeableServices;

        $arr = $cs
            ->map(function (ChargeableServiceModel $cs) {
                return [
                    'id'           => $cs->id,
                    'is_fulfilled' => true,
                    'total_time'   => ChargeableServiceModel::BHI === $cs->code ? $this->bhi_time : $this->getBillableCcmCs(),
                ];
            })
            ->values()
            ->toArray();

        return self::collection($arr);
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'           => $this->id,
            'total_time'   => $this->total_time,
            'is_fulfilled' => $this->is_fulfilled,
        ];
    }
}
