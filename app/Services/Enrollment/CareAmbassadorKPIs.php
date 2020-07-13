<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Enrollment;

use App\CareAmbassadorLog;
use CircleLinkHealth\Customer\Entities\User;

class CareAmbassadorKPIs
{
    /**
     * @var User
     */
    protected $careAmbassadorUser;
    /**
     * @var string
     */
    protected $end;

    /**
     * @var string
     */
    protected $start;

    public function __construct(User $careAmbassadorUser, string $start, string $end)
    {
        $this->careAmbassadorUser = $careAmbassadorUser;
        $this->start              = $start;
        $this->end                = $end;
    }

    public static function get(User $careAmbassadorUser, string $start, string $end)
    {
        return (new static($careAmbassadorUser, $start, $end))->makeStats();
    }

    private function makeStats(): array
    {
        $data = [];

        $input = $request->input();

        if (isset($input['start_date'], $input['end_date'])) {
            $start = Carbon::parse($input['start_date'])->startOfDay()->toDateString();
            $end   = Carbon::parse($input['end_date'])->endOfDay()->toDateString();
        } else {
            $start = Carbon::now()->startOfDay()->subWeek()->toDateString();
            $end   = Carbon::now()->endOfDay()->toDateString();
        }

        $careAmbassadors = User::whereHas('roles', function ($q) {
            $q->where('name', 'care-ambassador');
        })->pluck('id');

        $data = [];

        foreach ($careAmbassadors as $ambassadorUser) {
            $ambassador = User::find($ambassadorUser)->careAmbassador;

            if ( ! $ambassador) {
                continue;
            }
            $base = CareAmbassadorLog::where('enroller_id', $ambassador->id)
                ->where('day', '>=', $start)
                ->where('day', '<=', $end);

            $hourCost = $ambassador->hourly_rate ?? 'Not Set';

            $totalTimeInSystemSeconds = $base->sum('total_time_in_system');

            $data[$ambassador->id]['hourly_rate'] = $hourCost;

            $data[$ambassador->id]['name'] = User::find($ambassadorUser)->getFullName();

            $data[$ambassador->id]['total_hours'] = floatval(round($totalTimeInSystemSeconds / 3600, 2));

            $data[$ambassador->id]['total_seconds'] = $totalTimeInSystemSeconds;

            $data[$ambassador->id]['no_enrolled'] = $base->sum('no_enrolled');

            $data[$ambassador->id]['mins_per_enrollment'] = (0 != $base->sum('no_enrolled'))
                ?
                number_format(($totalTimeInSystemSeconds / 60) / $base->sum('no_enrolled'), 2)
                : 0;

            $data[$ambassador->id]['total_calls'] = $base->sum('total_calls');

            if (0 != $base->sum('total_calls') && 0 != $base->sum('no_enrolled') && 'Not Set' != $hourCost && 0 !== $totalTimeInSystemSeconds) {
                $data[$ambassador->id]['earnings'] = '$'.number_format(
                    $hourCost * ($totalTimeInSystemSeconds / 3600),
                    2
                );

                $data[$ambassador->id]['calls_per_hour'] = number_format(
                    $base->sum('total_calls') / ($totalTimeInSystemSeconds / 3600),
                    2
                );

                $data[$ambassador->id]['conversion'] = number_format(
                    ($base->sum('no_enrolled') / $base->sum('total_calls')) * 100,
                    2
                ).'%';

                $data[$ambassador->id]['per_cost'] = '$'.number_format(
                    (($totalTimeInSystemSeconds / 3600) * $hourCost) / $base->sum('no_enrolled'),
                    2
                );
            } else {
                $data[$ambassador->id]['earnings']       = 'N/A';
                $data[$ambassador->id]['conversion']     = 'N/A';
                $data[$ambassador->id]['calls_per_hour'] = 'N/A';
                $data[$ambassador->id]['per_cost']       = 'N/A';
            }
        }

        return $data;
    }
}
