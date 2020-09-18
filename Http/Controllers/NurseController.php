<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Http\Controllers;

use CircleLinkHealth\CpmAdmin\Exports\CareCoachMonthlyReport;
use CircleLinkHealth\CpmAdmin\Filters\NurseDailyReportFilters;
use CircleLinkHealth\CpmAdmin\Services\NurseDailyReport;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;

class NurseController extends Controller
{
    public function dailyReport(Request $request, NurseDailyReportFilters $filters)
    {
        $fields    = ['*'];
        $byColumn  = $request->get('byColumn');
        $query     = $request->get('query');
        $limit     = $request->get('limit');
        $orderBy   = $request->get('orderBy');
        $ascending = $request->get('ascending');
        $page      = $request->get('page');

        $nursesQuery = User::careCoaches()
            ->whereHas('pageTimersAsProvider', function ($t) {
                $t->whereNotNull('end_time');
            })
            ->where('access_disabled', 0)
            ->filter($filters);

        $count = $nursesQuery->count();

        if (isset($orderBy)) {
            if ('name' === $orderBy) {
                $orderBy = 'first_name';
            }
            $direction = 1 == $ascending
                ? 'ASC'
                : 'DESC';
            $nursesQuery->orderBy($orderBy, $direction);
        }

        $nursesQuery->limit($limit)
            ->skip($limit * ($page - 1));

        $nurses = $nursesQuery->get();
        $report = NurseDailyReport::data(Carbon::now(), $nurses, isset($orderBy));

        return response()->json([
            'data'  => $report->toArray(),
            'count' => $count,
        ]);
    }

    public function makeDailyReport()
    {
        return view('admin.reports.nursedaily');
    }

    public function makeHourlyStatistics()
    {
//        $data = (new NurseCallStatistics(Nurse::all(),
//                                Carbon::parse('2016-09-29 09:00:00'),
//                                Carbon::parse('2016-09-29 10:00:00')))
//            ->nurseCallsPerHour();

        return view('statistics.nurses.info');
    }

    public function monthlyReport(Request $request)
    {
        $date = Carbon::now();
        if ($request['date']) {
            $date = new Carbon($request['date']);
        }
        $report = new CareCoachMonthlyReport($date);
        $rows   = $report->collection();

        if ($request->has('json')) {
            return response()->json($rows);
        }
        if ($request->has('excel')) {
            return $report;
        }

        $currentPage              = LengthAwarePaginator::resolveCurrentPage();
        $perPage                  = 100;
        $currentPageSearchResults = $rows->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $rows                     = new LengthAwarePaginator($currentPageSearchResults, count($rows), $perPage);

        $rows = $rows->withPath('admin/reports/nurse/monthly');

        return view('admin.nurse.monthly-report', compact(['date', 'rows']));
    }
}
