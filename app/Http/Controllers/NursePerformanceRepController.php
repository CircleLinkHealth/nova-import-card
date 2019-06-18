<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Services\NursesPerformanceReportService;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class NursePerformanceRepController extends Controller
{
    /**
     * @var NursesPerformanceReportService
     */
    private $service;

    /**
     * NursePerformanceRepController constructor.
     *
     * @param NursesPerformanceReportService $service
     */
    public function __construct(NursesPerformanceReportService $service)
    {
        $this->service = $service;
    }

    /**
     * @param $reportPerDay
     *
     * @return string
     */
    public function getCaseLoadComplete($reportPerDay)
    {
        return array_key_exists('caseLoadComplete', $reportPerDay)
            ? $reportPerDay['caseLoadComplete'] : 'N/A';
    }

    /**
     * @param $reportPerDay
     *
     * @return string
     */
    public function getCaseLoadNeededToComplete($reportPerDay)
    {
        return array_key_exists('caseLoadNeededToComplete', $reportPerDay)
            ? $reportPerDay['caseLoadNeededToComplete'] : 'N/A';
    }

    /**
     * @param $reportPerDay
     *
     * @return string
     */
    public function getCompletionRate($reportPerDay)
    {
        return array_key_exists('completionRate', $reportPerDay)
            ? $reportPerDay['completionRate'] : 'N/A';
    }

    /**
     * Gets input date and collects days from that date back to beginning of that week.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     *
     * @return array
     */
    public function getDaysBetweenPeriodRange(Carbon $startDate, Carbon $endDate)
    {
        $days = [];
        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            $days[] = $date->copy();
        }

        return $days;
    }

    /**
     * @param $reportPerDay
     *
     * @return string
     */
    public function getEfficiencyIndex($reportPerDay)
    {
        return array_key_exists('efficiencyIndex', $reportPerDay)
            ? $reportPerDay['efficiencyIndex'] : 'N/A';
    }

    /**
     * @param $dates
     *
     * @return mixed
     */
    public function getEndDate($dates)
    {
        return $dates['endDate'];
    }

    /**
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return Collection
     */
    public function getNursePerformanceData(Request $request)
    {
        $startDate      = Carbon::parse($request['start_date']);
        $endDate        = Carbon::parse($request['end_date']);
        $days           = $this->getDaysBetweenPeriodRange($startDate, $endDate);
        $nurses         = $this->service->manipulateData($days);
        $nurseDailyData = $this->getNursesDailyData($nurses);

        return collect($nurseDailyData);
    }

    /**
     * @param Collection $nurses
     *
     * @return array
     */
    public function getNursesDailyData(Collection $nurses)
    {
        $data = $nurses->except('totals');
        //@todo:one level of indendetion
        $nurseDailyData = [];
        $n              = 0;
        foreach ($data as $name => $report) {
            foreach ($report as $day => $reportPerDay) {
                $nurseDailyData[$n]['weekDay']                   = Carbon::parse($day)->copy()->format('D jS');
                $nurseDailyData[$n]['name']                      = $reportPerDay['nurse_full_name'];
                $nurseDailyData[$n]['actualHours']               = $reportPerDay['actualHours'];
                $nurseDailyData[$n]['committedHours']            = $reportPerDay['committedHours'];
                $nurseDailyData[$n]['scheduledCalls']            = $reportPerDay['scheduledCalls'];
                $nurseDailyData[$n]['actualCalls']               = $reportPerDay['actualCalls'];
                $nurseDailyData[$n]['successful']                = $reportPerDay['successful'];
                $nurseDailyData[$n]['unsuccessful']              = $reportPerDay['unsuccessful'];
                $nurseDailyData[$n]['completionRate']            = $this->getCompletionRate($reportPerDay);
                $nurseDailyData[$n]['efficiencyIndex']           = $this->getEfficiencyIndex($reportPerDay);
                $nurseDailyData[$n]['caseLoadComplete']          = $this->getCaseLoadComplete($reportPerDay);
                $nurseDailyData[$n]['caseLoadNeededToComplete']  = $this->getCaseLoadNeededToComplete($reportPerDay);
                $nurseDailyData[$n]['hoursCommittedRestOfMonth'] = $this->hoursCommittedRestOfMonth($reportPerDay);
                $nurseDailyData[$n]['surplusShortfallHours']     = $this->surplusShortfallHours($reportPerDay);
                ++$n;
            }
        }
//        $nurseDailyData['totals'] = $this->getNursesDailyTotals($nurses);

        return $nurseDailyData;
//        return collect(
//            [
//                'nurseMetrics' => $nurseDailyData,
//                'totals'       => $nurseDailyData['totals'],
//            ]
//        );
    }

    /**
     * @param Collection $nurses
     *
     * @return array
     */
    public function getNursesDailyTotals(Collection $nurses)
    {
        $nurseDailyTotals = [];
        $n                = 0;
        $totals           = $nurses->only('totals');
        foreach ($totals as $total => $totalsPerDay) {
            foreach ($totalsPerDay as $totalsForDay) {
                $nurseDailyTotals[$n]['scheduledCallsSum']         = $totalsForDay['scheduledCallsSum'];
                $nurseDailyTotals[$n]['actualCallsSum']            = $totalsForDay['actualCallsSum'];
                $nurseDailyTotals[$n]['successfulCallsSum']        = $totalsForDay['successfulCallsSum'];
                $nurseDailyTotals[$n]['unsuccessfulCallsSum']      = $totalsForDay['unsuccessfulCallsSum'];
                $nurseDailyTotals[$n]['actualHoursSum']            = $totalsForDay['actualHoursSum'];
                $nurseDailyTotals[$n]['committedHoursSum']         = $totalsForDay['committedHoursSum'];
                $nurseDailyTotals[$n]['completionRate']            = $totalsForDay->has('completionRate') ? $totalsForDay['completionRate'] : 'N/A';
                $nurseDailyTotals[$n]['efficiencyIndex']           = $totalsForDay->has('efficiencyIndex') ? $totalsForDay['efficiencyIndex'] : 'N/A';
                $nurseDailyTotals[$n]['caseLoadNeededToComplete']  = $totalsForDay->has('caseLoadNeededToComplete') ? $totalsForDay['caseLoadNeededToComplete'] : 'N/A';
                $nurseDailyTotals[$n]['hoursCommittedRestOfMonth'] = $totalsForDay->has('hoursCommittedRestOfMonth') ? $totalsForDay['hoursCommittedRestOfMonth'] : 'N/A';
                $nurseDailyTotals[$n]['surplusShortfallHours']     = $totalsForDay->has('surplusShortfallHours') ? $totalsForDay['surplusShortfallHours'] : 'N/A';
                $nurseDailyTotals[$n]['caseLoadComplete']          = $totalsForDay->has('caseLoadComplete') ? $totalsForDay['caseLoadComplete'] : 'N/A';

                ++$n;
            }
        }

        return $nurseDailyTotals;
    }

    /**
     * @param $dates
     *
     * @return mixed
     */
    public function getStartDate($dates)
    {
        return $dates['startDate'];
    }

    /**
     * @param mixed $reportPerDay
     *
     * @return string
     */
    public function hoursCommittedRestOfMonth($reportPerDay)
    {
        return array_key_exists('hoursCommittedRestOfMonth', $reportPerDay)
            ? $reportPerDay['hoursCommittedRestOfMonth'] : 'N/A';
    }

    /**
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return Factory|View
     */
    public function nurseMetricsDashboard(Request $request)
    {
        $yesterdayDate = Carbon::yesterday()->startOfDay();
        $limitDate     = $this->service->getLimitDate();
        $dates         = $this->setDates($request, $yesterdayDate);
        $startDate     = $this->getStartDate($dates);
        $endDate       = $this->getEndDate($dates);

        return view(
            'admin.reports.nursePerformance',
            compact('endDate', 'yesterdayDate', 'limitDate', 'startDate')
        );
    }

    /**
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function nurseMetricsPerformanceData(Request $request)
    {
        return datatables()->collection($this->getNursePerformanceData($request))->make(true);
    }

    /**
     * @param Request $request
     * @param Carbon  $yesterdayDate
     *
     * @return array|RedirectResponse
     */
    public function setDates(Request $request, Carbon $yesterdayDate)
    {
        $request->has('start_date') && $request->has('end_date')
            ?
            [
                $startDate = Carbon::parse($request['start_date']),
                $endDate = Carbon::parse($request['end_date']),
            ]
            :
            [
                $endDate = $yesterdayDate->copy()->endOfDay(),
                $startDate = $yesterdayDate->copy()->startOfDay(),
            ];

        $this->validateInputDate($startDate, $endDate);

        return [
            'startDate' => $startDate,
            'endDate'   => $endDate,
        ];
    }

    /**
     * @param $reportPerDay
     *
     * @return string
     */
    public function surplusShortfallHours($reportPerDay)
    {
        return array_key_exists('surplusShortfallHours', $reportPerDay)
            ? $reportPerDay['surplusShortfallHours'] : 'N/A';
    }

    /**
     * @param Carbon $startDate
     * @param Carbon $endDate
     *
     * @return RedirectResponse
     */
    public function validateInputDate(Carbon $startDate, Carbon $endDate)
    {
        if ($endDate->gte(today()->startOfDay()) || $startDate->gte(today()->startOfDay())) {
            $messages['errors'][] = 'Please input a past date';

            return redirect()->back()->withErrors($messages);
        }
    }
}
