<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Enrollment;

use App\Http\Controllers\Controller;
use App\Http\Resources\CareAmbassadorKPIsCSVResourceCollection;
use App\Http\Resources\PracticeKPIsCSVResourceCollection;
use App\Services\Enrollment\CareAmbassadorKPIs;
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
        $input = $request->input();

        if (isset($input['start_date'], $input['end_date'])) {
            $start = Carbon::parse($input['start_date'])->startOfDay();
            $end   = Carbon::parse($input['end_date'])->endOfDay();
        } else {
            $start = Carbon::now()->subWeek()->startOfDay();
            $end   = Carbon::now()->endOfDay();
        }

        $fields = ['*'];

        $byColumn  = $request->get('byColumn');
        $query     = json_decode($request->get('query'));
        $limit     = $request->get('limit');
        $orderBy   = $request->get('orderBy');
        $ascending = $request->get('ascending');
        $page      = $request->get('page');

        $careAmbassadorName = $query && property_exists($query, 'name') ? $query->name : null;

        $caQuery = User::ofType('care-ambassador')
            ->with(['careAmbassador'])
            ->has('careAmbassador')
            ->when($careAmbassadorName, function ($q) use ($careAmbassadorName) {
                return $q->where('display_name', 'like', "%{$careAmbassadorName}%");
            })
            ->select($fields);

        $count = $caQuery->count();

        $caQuery->limit($limit)
            ->skip($limit * ($page - 1));

        if (isset($orderBy)) {
            $direction = 1 == $ascending
                ? 'ASC'
                : 'DESC';
            $caQuery->orderBy($orderBy, $direction);
        }

        if ($request->has('csv')) {
            return CareAmbassadorKPIsCSVResourceCollection::make($caQuery->paginate($input['rows']))->setTimeRange($start, $end);
        }

        $careAmbassadors = $caQuery->get();

        $stats = [];

        foreach ($careAmbassadors as $ca) {
            $stats[] = CareAmbassadorKPIs::get($ca, $start, $end);
        }

        return [
            'data'  => $stats,
            'count' => $count,
        ];
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
}
