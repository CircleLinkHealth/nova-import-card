<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Http\Resources;

use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlyTime;
use CircleLinkHealth\CcmBilling\Entities\PatientForcedChargeableService;
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
        $forcedServices = $user->forcedChargeableServices;

        $services = $user->chargeableMonthlySummaries
            ->map(function (ChargeablePatientMonthlySummary $item) use ($user, $forcedServices) {
                $patientForcedCs = $forcedServices->firstWhere(
                    'chargeable_service_id',
                    $csId = ChargeableServiceModel::getChargeableServiceIdUsingCode(
                        ChargeableServiceModel::getBaseCode(
                            $item->chargeableService->code
                        )
                    )
                );

                /** @var ChargeablePatientMonthlyTime $time */
                $time = $user->chargeableMonthlyTime->firstWhere('chargeable_service_id', $csId);

                return [
                    'id'           => $item->chargeable_service_id,
                    'is_fulfilled' => $item->is_fulfilled,
                    'total_time'   => optional($time)->total_time ?? 0,
                    'is_blocked'   => PatientForcedChargeableService::BLOCK_ACTION_TYPE === optional($patientForcedCs)->action_type,
                    'is_forced'    => PatientForcedChargeableService::FORCE_ACTION_TYPE === optional($patientForcedCs)->action_type,
                ];
            })
            ->values();

        $user->chargeableMonthlyTime->each(function ($time) use ($services, $forcedServices) {
            $patientForcedCs = $forcedServices->firstWhere('chargeable_service_id', $time->chargeable_service_id);
            $entry = $services->firstWhere('chargeable_service_id', $time->chargeable_service_id);
            if ( ! $entry) {
                $services->push([
                    'id'           => $time->chargeable_service_id,
                    'is_fulfilled' => false,
                    'total_time'   => $time->total_time,
                    'is_blocked'   => PatientForcedChargeableService::BLOCK_ACTION_TYPE === optional($patientForcedCs)->action_type,
                    'is_forced'    => PatientForcedChargeableService::FORCE_ACTION_TYPE === optional($patientForcedCs)->action_type,
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
            ->map(function (ChargeableServiceModel $cs) use ($pms) {
                return [
                    'id'           => $cs->id,
                    'is_fulfilled' => true,
                    'total_time'   => ChargeableServiceModel::BHI === $cs->code ? $pms->bhi_time : $pms->getBillableCcmCs(),
                    'is_forced'    => null,
                    'is_blocked'   => null,
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
            'id'           => $this['id'],
            'total_time'   => $this['total_time'],
            'is_fulfilled' => $this['is_fulfilled'],
            'is_blocked'   => $this['is_blocked'],
            'is_forced'    => $this['is_forced'],
        ];
    }
}
