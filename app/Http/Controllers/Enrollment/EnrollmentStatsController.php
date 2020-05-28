<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Enrollment;

use App\CareAmbassadorLog;
use App\Http\Controllers\Controller;
use App\Http\Resources\PracticeKPIsCSVResourceCollection;
use App\Services\Enrollment\PracticeKPIs;
use Carbon\Carbon;
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
        $stats = collect($this->getAmbassadorStats($request))->map(function ($ps) {
            return $ps;
        })->values()->toArray();

        return response()->json($stats);
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
     * Render practice stats vue table.
     *
     * @return mixed
     */
    public function practiceStats(Request $request)
    {
        $input = $request->input();

        if (isset($input['start_date'], $input['end_date'])) {
            $start = Carbon::parse($input['start_date'])->startOfDay()->toDateTimeString();
            $end   = Carbon::parse($input['end_date'])->endOfDay()->toDateTimeString();
        } else {
            $start = Carbon::now()->subWeek()->startOfDay()->toDateTimeString();
            $end   = Carbon::now()->endOfDay()->toDateTimeString();
        }

        $practiceIds = DB::table('enrollees')
            ->where('status', '!=', Enrollee::LEGACY)
            ->distinct('practice_id')->pluck('practice_id')
            ->filter()
            ->toArray();

        $fields = ['*'];

        $byColumn  = $request->get('byColumn');
        $query     = json_decode($request->get('query'));
        $limit     = $request->get('limit');
        $orderBy   = $request->get('orderBy');
        $ascending = $request->get('ascending');
        $page      = $request->get('page');

        $practiceName = $query && property_exists($query, 'name') ? $query->name : null;

        $practiceQuery = Practice::active()
            ->whereIn('id', $practiceIds)
            ->when($practiceName, function ($q) use ($practiceName) {
                $q->where('display_name', 'like', "%{$practiceName}%");
            })
            ->select($fields);

        $count = $practiceQuery->count();

        $practiceQuery->limit($limit)
            ->skip($limit * ($page - 1));

        if (isset($orderBy)) {
            $direction = 1 == $ascending
                ? 'ASC'
                : 'DESC';
            $practiceQuery->orderBy($orderBy, $direction);
        }

        if ($request->has('csv')) {
            return PracticeKPIsCSVResourceCollection::make($practiceQuery->paginate($input['rows']))->setTimeRange($start, $end);
        }

        $practices = $practiceQuery->get();

        $stats = [];

        foreach ($practices as $practice) {
            $stats[] = PracticeKPIs::get($practice, $start, $end);
        }

        return [
            'data'  => $stats,
            'count' => $count,
        ];
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
