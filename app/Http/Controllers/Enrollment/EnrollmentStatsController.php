<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Enrollment;

use App\CareAmbassador;
use App\CareAmbassadorLog;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use CircleLinkHealth\Core\Exports\FromArray;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnrollmentStatsController extends Controller
{
    /**
     * Render ambassador stats datatable.
     *
     * @return mixed
     */
    public function ambassadorStats(Request $request)
    {
        return datatables()->collection(collect($this->getAmbassadorStats($request)))->make(true);
    }

    /**
     * Get an excel representation of ambassador stats.
     *
     * @return mixed
     */
    public function ambassadorStatsExcel(Request $request)
    {
        $date = Carbon::now()->toAtomString();
        $data = $this->getAmbassadorStats($request);

        $fileName = "Care Ambassador Enrollment Stats - ${date}.xls";

        return (new FromArray($fileName, $data))->download($fileName);
    }

    /**
     * Show the page to request ambassador stats.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function makeAmbassadorStats()
    {
        return view('admin.reports.enrollment.ambassador-kpis');
    }

    /**
     * Show the page to request practice stats.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function makePracticeStats()
    {
        return view('admin.reports.enrollment.practice-kpis');
    }

    /**
     * Render practice stats datatable.
     *
     * @return mixed
     */
    public function practiceStats(Request $request)
    {
        return datatables()->collection(collect($this->getPracticeStats($request)))
            ->make(true);
    }

    /**
     * Get an excel representation of practice stats.
     *
     * @return mixed
     */
    public function practiceStatsExcel(Request $request)
    {
        $date = Carbon::now()->toAtomString();
        $data = $this->getPracticeStats($request);

        $filename = "Practice Enrollment Stats - ${date}.xlsx";

        return (new FromArray($filename, $data))->download($filename);
    }

    /**
     * Get Ambassador stats.
     *
     * @return array
     */
    private function getAmbassadorStats(Request $request)
    {
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

            $data[$ambassador->id]['hourly_rate'] = $hourCost;

            $data[$ambassador->id]['name'] = User::find($ambassadorUser)->getFullName();

            $data[$ambassador->id]['total_hours'] = floatval(round($base->sum('total_time_in_system') / 3600, 2));

            $data[$ambassador->id]['no_enrolled']         = $base->sum('no_enrolled');
            $data[$ambassador->id]['mins_per_enrollment'] = (0 != $base->sum('no_enrolled'))
                ?
                number_format(($base->sum('total_time_in_system') / 60) / $base->sum('no_enrolled'), 2)
                : 0;

            $data[$ambassador->id]['total_calls'] = $base->sum('total_calls');

            if (0 != $base->sum('total_calls') && 0 != $base->sum('no_enrolled') && 'Not Set' != $hourCost) {
                $data[$ambassador->id]['earnings'] = '$'.number_format(
                    $hourCost * ($base->sum('total_time_in_system') / 3600),
                    2
                );

                $data[$ambassador->id]['calls_per_hour'] = number_format(
                    $base->sum('total_calls') / ($base->sum('total_time_in_system') / 3600),
                    2
                );

                $data[$ambassador->id]['conversion'] = number_format(
                    ($base->sum('no_enrolled') / $base->sum('total_calls')) * 100,
                    2
                ).'%';

                $data[$ambassador->id]['per_cost'] = '$'.number_format(
                    (($base->sum('total_time_in_system') / 3600) * $hourCost) / $base->sum('no_enrolled'),
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

    /**
     * Get practice stats.
     *
     * @return array
     */
    private function getPracticeStats(Request $request)
    {
        $input = $request->input();

        if (isset($input['start_date'], $input['end_date'])) {
            $start = Carbon::parse($input['start_date'])->startOfDay()->toDateTimeString();
            $end   = Carbon::parse($input['end_date'])->endOfDay()->toDateTimeString();
        } else {
            $start = Carbon::now()->subWeek()->startOfDay()->toDateTimeString();
            $end   = Carbon::now()->endOfDay()->toDateTimeString();
        }

        $practices = DB::table('enrollees')->distinct('practice_id')->pluck('practice_id')->filter();

        $data = [];

        foreach ($practices as $practiceId) {
            $practice = Practice::findOrFail($practiceId);

            $data[$practice->id]['name'] = $practice->display_name;

            $data[$practice->id]['unique_patients_called'] = Enrollee::where('practice_id', $practice->id)
                ->where('last_attempt_at', '>=', $start)
                ->where('last_attempt_at', '<=', $end)
                ->where(function ($q) {
                                                                         $q->where('status', 'utc')
                                                                             ->orWhere('status', 'consented')
                                                                             ->orWhere('status', 'rejected');
                                                                     })
                ->count();

            $data[$practice->id]['consented'] = Enrollee
                ::where('practice_id', $practice->id)
                    ->where('last_attempt_at', '>=', $start)
                    ->where('last_attempt_at', '<=', $end)->where('status', 'consented')->count();

            $data[$practice->id]['utc'] = Enrollee
                ::where('practice_id', $practice->id)
                    ->where('last_attempt_at', '>=', $start)
                    ->where('last_attempt_at', '<=', $end)->where('status', 'utc')->count();

            $data[$practice->id]['hard_declined'] = Enrollee
                ::where('practice_id', $practice->id)
                    ->where('last_attempt_at', '>=', $start)
                    ->where('last_attempt_at', '<=', $end)
                    ->where('status', 'rejected')
                    ->count();

            $data[$practice->id]['soft_declined'] = Enrollee
                ::where('practice_id', $practice->id)
                    ->where('last_attempt_at', '>=', $start)
                    ->where('last_attempt_at', '<=', $end)
                    ->where('status', 'soft_rejected')
                    ->count();

            $total_time = Enrollee
                ::where('practice_id', $practice->id)
                    ->where('last_attempt_at', '>=', $start)
                    ->where('last_attempt_at', '<=', $end)
                    ->sum('total_time_spent');

            $data[$practice->id]['labor_hours'] = secondsToHMS($total_time);

            $enrollers = Enrollee::select(DB::raw('care_ambassador_user_id, sum(total_time_spent) as total'))
                ->where('practice_id', $practice->id)
                ->where('last_attempt_at', '>=', $start)
                ->where('last_attempt_at', '<=', $end)
                ->groupBy('care_ambassador_user_id')->pluck('total', 'care_ambassador_user_id');

            $data[$practice->id]['total_cost'] = 0;

            foreach ($enrollers as $enrollerId => $time) {
                if (empty($enrollerId)) {
                    continue;
                }

                $enroller = CareAmbassador::where('user_id', $enrollerId)->first();
                if ( ! $enroller) {
                    continue;
                }
                $data[$practice->id]['total_cost'] += number_format($enroller->hourly_rate * $time / 3600, 2);
            }

            if ($data[$practice->id]['unique_patients_called'] > 0 && $data[$practice->id]['consented'] > 0) {
                $data[$practice->id]['conversion'] = number_format(
                    $data[$practice->id]['consented'] / $data[$practice->id]['unique_patients_called'] * 100,
                    2
                ).'%';
            } else {
                $data[$practice->id]['conversion'] = 'N/A';
            }

            if ($data[$practice->id]['total_cost'] > 0 && $data[$practice->id]['consented'] > 0) {
                $data[$practice->id]['acq_cost'] = '$'.number_format(
                    $data[$practice->id]['total_cost'] / $data[$practice->id]['consented'],
                    2
                );
            } else {
                $data[$practice->id]['acq_cost'] = 'N/A';
            }

            if ($data[$practice->id]['total_cost'] > 0 && $total_time > 0) {
                $data[$practice->id]['labor_rate'] = '$'.number_format(
                    $data[$practice->id]['total_cost'] / ($total_time / 3600),
                    2
                );
            } else {
                $data[$practice->id]['labor_rate'] = 'N/A';
            }

            $data[$practice->id]['total_cost'] = '$'.$data[$practice->id]['total_cost'];
        }

        return $data;
    }
}
