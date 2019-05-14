<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Exports\CareCoachMonthlyReport;
use App\Jobs\CreateNurseInvoices;
use App\Jobs\GenerateNurseInvoice;
use App\Notifications\NurseInvoiceCreated;
use App\Reports\NurseDailyReport;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class NurseController extends Controller
{
    public function dailyReport()
    {
        return datatables()->collection(NurseDailyReport::data())->make(true);
    }

    public function generateInvoice(Request $request)
    {
        $input = $request->input();

        $nurseIds = $request->input('nurses');

        $addTime = $request->input('manual_time')
            ? $request->input('manual_time')
            : 0;

        $addNotes = $request->input('manual_time_notes')
            ? $request->input('manual_time_notes')
            : '';

        $variablePay = isset($input['alternative_pay']);

        $startDate = Carbon::parse('2018-12-01'/*$request->input('start_date')*/);
        $endDate   = Carbon::parse($request->input('end_date'));

        $selectAllNursesForTimePeriod = isset($input['all_selected_nurses']);

        if ($selectAllNursesForTimePeriod) {
            $nurses   = selectAllNursesForSelectedPeriod($startDate, $endDate);
            $nurseIds = [];
            foreach ($nurses as $key => $name) {
                $nurseIds[] = $key;
            }
        }

        if ('download' == $request->input('submit')) {
            GenerateNurseInvoice::dispatch(
                $nurseIds,
                $startDate,
                $endDate,
                auth()->user()->id,
                $variablePay,
                $addTime,
                $addNotes
            )->onQueue('demanding');
        } elseif ('downloadV2' == $request->input('submit')) {
            CreateNurseInvoices::dispatchNow(
                $nurseIds,
                $startDate,
                $endDate,
                auth()->user()->id,
                $variablePay,
                $addTime,
                $addNotes
            );
        }

        return 'Bessie Bots is crunching the invoices you requested. <br> She will send you an email when everything is done. You can always see previous jobs completed at '.link_to('/jobs/completed');
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

    public function makeInvoice()
    {
        $nurses = activeNurseNames();

        return view(
            'billing.nurse.create',
            [
                'nurses' => $nurses->sort(),
            ]
        );
    }

    public function monthlyOverview(Request $request)
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

    public function sendInvoice(Request $request)
    {
        $invoices = (array) json_decode($request->input('links'));
        $month    = $request->input('month');

        foreach ($invoices as $key => $value) {
            $value = (array) $value;

            $user = User::find($key);

            $user->notify(new NurseInvoiceCreated($value['link'], $month));
        }

        return redirect()->route('admin.reports.nurse.invoice')->with(['success' => 'yes']);
    }
}
