<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Exports\CareCoachMonthlyReport;
use App\Filters\NurseDailyReportFilters;
use App\Reports\NurseDailyReport;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

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
        $report = NurseDailyReport::data(Carbon::now(), $nurses, true);

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

    public function monthlyOverview(Request $request)
    {
        return $request->has('v2') ? $this->allocationV2($request) : $this->allocation($request);
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

    private function allocation(Request $request)
    {
        $input = $request->input();

        if (isset($input['next'])) {
            $dayCounter = Carbon::parse($input['next'])->firstOfMonth()->startOfDay();
            $last       = Carbon::parse($input['next'])->lastOfMonth()->endOfDay();
        } elseif (isset($input['previous'])) {
            $dayCounter = Carbon::parse($input['previous'])->firstOfMonth()->startOfDay();
            $last       = Carbon::parse($input['previous'])->lastOfMonth()->endOfDay();
        } else {
            $dayCounter = Carbon::now()->firstOfMonth()->startOfDay();
            $last       = Carbon::now()->lastOfMonth()->endOfDay();
        }

        $nurses = User::ofType('care-center')->where('access_disabled', 0)->get();
        $data   = [];

        while ($dayCounter->lte($last)) {
            foreach ($nurses as $nurse) {
                if ( ! $nurse->nurseInfo) {
                    continue;
                }

                $countScheduled = $nurse->nurseInfo->countScheduledCallsFor($dayCounter);

                $countMade = $nurse->nurseInfo->countCompletedCallsFor($dayCounter);

                $formattedDate = $dayCounter->format('m/d Y');

                $name = $nurse->first_name[0].'. '.$nurse->getLastName();

                if ($countScheduled > 0) {
                    $data[$formattedDate][$name]['Scheduled'] = $countScheduled;
                } else {
                    $data[$formattedDate][$name]['Scheduled'] = 0;
                }

                if ($countMade > 0) {
                    $data[$formattedDate][$name]['Actual Made'] = $countMade;
                } else {
                    $data[$formattedDate][$name]['Actual Made'] = 0;
                }
            }

            $dayCounter = $dayCounter->addDays(1);
        }

        return view(
            'admin.reports.allocation',
            [
                'data'  => $data,
                'month' => Carbon::parse($last),
            ]
        );
    }

    private function allocationV2(Request $request)
    {
        $input = $request->input();

        if (isset($input['next'])) {
            $dayCounter = Carbon::parse($input['next'])->firstOfMonth()->startOfDay();
            $last       = Carbon::parse($input['next'])->lastOfMonth()->endOfDay();
        } elseif (isset($input['previous'])) {
            $dayCounter = Carbon::parse($input['previous'])->firstOfMonth()->startOfDay();
            $last       = Carbon::parse($input['previous'])->lastOfMonth()->endOfDay();
        } else {
            $dayCounter = Carbon::now()->firstOfMonth()->startOfDay();
            $last       = Carbon::now()->lastOfMonth()->endOfDay();
        }

        $nurses = User::select(['id', 'first_name', 'last_name'])
            ->with(['nurseInfo'])
            ->has('nurseInfo')
            ->ofType('care-center')
            ->where('access_disabled', 0)
            ->get();
        $data = [];

        while ($dayCounter->lte($last)) {
            foreach ($nurses as $nurse) {
                $formattedDate = $dayCounter->format('m/d Y');

                $name = $nurse->first_name[0].'. '.$nurse->getLastName();

                $data[$formattedDate][$name]['Scheduled'] = $nurse->nurseInfo->countScheduledCallsFor($dayCounter);

                $data[$formattedDate][$name]['Actual Made'] = $nurse->nurseInfo->countCompletedCallsFor($dayCounter);
            }

            $dayCounter = $dayCounter->addDays(1);
        }

        return view(
            'admin.reports.allocation',
            [
                'data'  => $data,
                'month' => Carbon::parse($last),
                'v2'    => true,
            ]
        );
    }
}
