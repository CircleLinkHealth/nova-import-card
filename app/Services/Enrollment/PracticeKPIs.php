<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Enrollment;

use CircleLinkHealth\Customer\Entities\CareAmbassador;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Support\Facades\DB;

class PracticeKPIs
{
    /**
     * @var string
     */
    protected $end;
    /**
     * @var Practice
     */
    protected $practice;

    /**
     * @var string
     */
    protected $start;

    public function __construct(Practice $practice, string $start, string $end)
    {
        $this->practice = $practice;
        $this->start    = $start;
        $this->end      = $end;
    }

    public static function get(Practice $practice, string $start, string $end)
    {
        return (new static($practice, $start, $end))->makeStats();
    }

    private function makeStats(): array
    {
        $data = [];

        $data['name'] = $this->practice->display_name;

        $data['unique_patients_called'] = Enrollee::where('practice_id', $this->practice->id)
            ->where('last_attempt_at', '>=', $this->start)
            ->where('last_attempt_at', '<=', $this->end)
            ->whereIn('status', [
                Enrollee::UNREACHABLE,
                Enrollee::CONSENTED,
                Enrollee::ENROLLED,
                Enrollee::REJECTED,
                Enrollee::SOFT_REJECTED,
            ])
            ->count();

        $data['consented'] = Enrollee::where('practice_id', $this->practice->id)
            ->whereNotNull('care_ambassador_user_id')
            ->where('last_attempt_at', '>=', $this->start)
            ->where('last_attempt_at', '<=', $this->end)
            ->whereIn('status', [Enrollee::CONSENTED, Enrollee::ENROLLED])
            ->count();

        $data['utc'] = Enrollee::where('practice_id', $this->practice->id)
            ->whereNotNull('care_ambassador_user_id')
            ->where('last_attempt_at', '>=', $this->start)
            ->where('last_attempt_at', '<=', $this->end)
            ->where('status', Enrollee::UNREACHABLE)
            ->count();

        $data['hard_declined'] = Enrollee::where('practice_id', $this->practice->id)
            ->whereNotNull('care_ambassador_user_id')
            ->where('last_attempt_at', '>=', $this->start)
            ->where('last_attempt_at', '<=', $this->end)
            ->where('status', Enrollee::REJECTED)
            ->count();

        $data['soft_declined'] = Enrollee::where('practice_id', $this->practice->id)
            ->whereNotNull('care_ambassador_user_id')
            ->where('last_attempt_at', '>=', $this->start)
            ->where('last_attempt_at', '<=', $this->end)
            ->where('status', Enrollee::SOFT_REJECTED)
            ->count();

        $total_time = Enrollee
                ::where('practice_id', $this->practice->id)
                    ->where('last_attempt_at', '>=', $this->start)
                    ->where('last_attempt_at', '<=', $this->end)
                    ->sum('total_time_spent');

        $data['labor_hours'] = secondsToHMS($total_time);

        $data['incomplete_3_attempts'] = Enrollee
                ::where('practice_id', $this->practice->id)
                    ->where('last_attempt_at', '>=', $this->start)
                    ->where('last_attempt_at', '<=', $this->end)
                    ->where('attempt_count', '>=', 3)
                    ->whereNotIn(
                        'status',
                        [Enrollee::UNREACHABLE, Enrollee::SOFT_REJECTED, Enrollee::REJECTED]
                    )
                    ->count();

        $enrollers = Enrollee::select(DB::raw('care_ambassador_user_id, sum(total_time_spent) as total'))
            ->where('practice_id', $this->practice->id)
            ->where('last_attempt_at', '>=', $this->start)
            ->where('last_attempt_at', '<=', $this->end)
            ->groupBy('care_ambassador_user_id')
            ->pluck('total', 'care_ambassador_user_id');

        $data['total_cost'] = 0;

        foreach ($enrollers as $enrollerId => $time) {
            if (empty($enrollerId)) {
                continue;
            }

            $enroller = CareAmbassador::where('user_id', $enrollerId)->first();
            if ( ! $enroller) {
                continue;
            }
            $data['total_cost'] += $enroller->hourly_rate * $time / 3600;
        }

        if ($data['unique_patients_called'] > 0 && $data['consented'] > 0) {
            $data['conversion'] = number_format(
                $data['consented'] / $data['unique_patients_called'] * 100,
                2
            ).'%';
        } else {
            $data['conversion'] = 'N/A';
        }

        if ($data['total_cost'] > 0 && $data['consented'] > 0) {
            $data['acq_cost'] = '$'.number_format(
                $data['total_cost'] / $data['consented'],
                2
            );
        } else {
            $data['acq_cost'] = 'N/A';
        }

        if ($data['total_cost'] > 0 && $total_time > 0) {
            $data['labor_rate'] = '$'.number_format(
                $data['total_cost'] / ($total_time / 3600),
                2
            );
        } else {
            $data['labor_rate'] = 'N/A';
        }

        $data['total_cost'] = '$'.number_format($data['total_cost'], 2);

        return $data;
    }
}
