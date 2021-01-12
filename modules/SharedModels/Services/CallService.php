<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Services;

use CircleLinkHealth\Customer\Actions\PatientTimeAndCalls;
use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\SharedModels\Entities\Call;
use CircleLinkHealth\SharedModels\Entities\CallViewNurses;
use Illuminate\Support\Collection;

class CallService
{
    /**
     * @param $dropdownStatus
     * @param $filterPriority
     * @param mixed $nurseId
     *
     * @return Builder[]|Collection
     */
    public function filterCalls($dropdownStatus, $filterPriority, string $today, $nurseId)
    {
        $calls = CallViewNurses::where('nurse_id', '=', $nurseId)
            ->whereIn('patient_assigned_nurse_id', [$nurseId, CpmConstants::SCHEDULER_POSTMARK_INBOUND_MAIL]);

        if ('completed' === $dropdownStatus && 'all' === $filterPriority) {
            $calls->whereIn('status', [Call::REACHED, Call::DONE]);
        }

        if ('scheduled' === $dropdownStatus && 'all' === $filterPriority) {
            $calls->where('status', '=', Call::SCHEDULED);
        }

        if ('all' !== $filterPriority) {
            // Case 1. Is scheduled but NOT asap with scheduled date <= today
            // Case 2. Is ASAP(asap is always status 'scheduled')
            $calls->where(function ($query) use ($today) {
                $query->where(
                    [
                        ['status', '=', Call::SCHEDULED],
                        ['scheduled_date', '<=', $today],
                    ]
                )->orWhere(
                    [
                        ['asap', '=', true],
                    ]
                );
            });
        }

        // Ordering: ASAP are always first, then Call Backs, then everything else with earlier tasks higher than later tasks.
        $calls = $calls->orderByRaw('asap desc, FIELD(type, "Call Back") desc, scheduled_date asc, call_time_start asc, call_time_end asc')->get();

        $patientSupplementaryData = PatientTimeAndCalls::get($calls->pluck('patient_id')->toArray());

        return $calls->transform(function ($c) use ($patientSupplementaryData) {
            $suppl = $patientSupplementaryData->filter(fn (\CircleLinkHealth\Customer\DTO\PatientTimeAndCalls $d) => $d->getPatientId() === $c->patient_id)->first();

            $c->ccm_total_time = $suppl->getCcmTotalTime();
            $c->bhi_total_time = $suppl->getBhiTotalTime();
            $c->pcm_total_time = $suppl->getPcmTotalTime();
            $c->rpm_total_time = $suppl->getRpmTotalTime();
            $c->rhc_total_time = $suppl->getRhcTotalTime();
            $c->no_of_calls = $suppl->getNoOfCalls();
            $c->no_of_successful_calls = $suppl->getNoOfSuccessfulCalls();

            return $c;
        });
    }
}
