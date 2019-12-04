<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\CallView;

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
        $calls = CallView::where('nurse_id', '=', $nurseId);

        if ('completed' === $dropdownStatus && 'all' === $filterPriority) {
            $calls->whereIn('status', ['reached', 'done']);
        }

        if ('scheduled' === $dropdownStatus && 'all' === $filterPriority) {
            $calls->where('status', '=', 'scheduled');
        }

        if ('all' !== $filterPriority) {
            // Case 1. Is scheduled but NOT asap with scheduled date <= today
            // Case 2. Is ASAP(asap is always status 'scheduled')
            $calls->where(function ($query) use ($today) {
                $query->where(
                    [
                        ['status', '=', 'scheduled'],
                        ['scheduled_date', '<=', $today],
                    ]
                )->orWhere(
                    [
                        ['asap', '=', true],
                    ]
                );
            });
        }

        $calls->orderByRaw("CASE WHEN type='Call Back' THEN type END desc, scheduled_date desc, call_time_start asc, call_time_end asc");

        return $calls->get();
    }
}
