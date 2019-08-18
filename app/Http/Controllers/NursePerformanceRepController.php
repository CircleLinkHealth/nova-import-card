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
    public function caseLoad($reportPerDay)
    {
        return array_key_exists('uniquePatientsAssignedForMonth', $reportPerDay)
            ? $reportPerDay['uniquePatientsAssignedForMonth'] : 'N/A';
    }

    /**
     * @param $reportPerDay
     *
     * @return string
     */
    public function caseLoadComplete($reportPerDay)
    {
        return array_key_exists('caseLoadComplete', $reportPerDay)
            ? $reportPerDay['caseLoadComplete'] : 'N/A';
    }

    /**
     * @param $reportPerDay
     *
     * @return string
     */
    public function caseLoadNeededToComplete($reportPerDay)
    {
        return array_key_exists('caseLoadNeededToComplete', $reportPerDay)
            ? $reportPerDay['caseLoadNeededToComplete'] : 'N/A';
    }

    /**
     * @param $reportPerDay
     *
     * @return string
     */
    public function completionRate($reportPerDay)
    {
        return array_key_exists('completionRate', $reportPerDay)
            ? $reportPerDay['completionRate'] : 'N/A';
    }

    /**
     * @param $reportPerDay
     *
     * @return string
     */
    public function efficiencyIndex($reportPerDay)
    {
        return array_key_exists('efficiencyIndex', $reportPerDay)
            ? $reportPerDay['efficiencyIndex'] : 'N/A';
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
     * @return array
     */
    public function getNursePerformanceData(Request $request)
    {
        $startDate = Carbon::parse($request['start_date']);
        $endDate   = Carbon::parse($request['end_date']);
        $days      = $this->getDaysBetweenPeriodRange($startDate, $endDate);
        $nurses    = $this->service->manipulateData($days);

        return $this->nursesDataForView($nurses);
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
     * @param Collection $nurses
     *
     * @return array
     */
    public function nursesDataForView(Collection $nurses)
    {
        //@todo:one level of indendetion
        $nurseDailyData = [];
        $n              = 0;
        foreach ($nurses as $name => $report) {
            foreach ($report as $day => $reportPerDay) {
                $nurseDailyData[$n]['weekDay']                   = Carbon::parse($day)->copy()->format('D jS');
                $nurseDailyData[$n]['name']                      = $reportPerDay['nurse_full_name'];
                $nurseDailyData[$n]['actualHours']               = $reportPerDay['actualHours'];
                $nurseDailyData[$n]['committedHours']            = $reportPerDay['committedHours'];
                $nurseDailyData[$n]['scheduledCalls']            = $reportPerDay['scheduledCalls'];
                $nurseDailyData[$n]['actualCalls']               = $reportPerDay['actualCalls'];
                $nurseDailyData[$n]['successful']                = $reportPerDay['successful'];
                $nurseDailyData[$n]['unsuccessful']              = $reportPerDay['unsuccessful'];
                $nurseDailyData[$n]['completionRate']            = $this->completionRate($reportPerDay);
                $nurseDailyData[$n]['efficiencyIndex']           = $this->efficiencyIndex($reportPerDay);
                $nurseDailyData[$n]['caseLoad']                  = $this->caseLoad($reportPerDay);
                $nurseDailyData[$n]['caseLoadComplete']          = $this->caseLoadComplete($reportPerDay);
                $nurseDailyData[$n]['caseLoadNeededToComplete']  = $this->caseLoadNeededToComplete($reportPerDay);
                $nurseDailyData[$n]['projectedHoursLeftInMonth'] = $this->projectedHoursLeftInMonth($reportPerDay);
                $nurseDailyData[$n]['hoursCommittedRestOfMonth'] = $this->hoursCommittedRestOfMonth($reportPerDay);
                $nurseDailyData[$n]['surplusShortfallHours']     = $this->surplusShortfallHours($reportPerDay);
                ++$n;
            }
        }

        return $nurseDailyData;
    }

    /**
     * @param $reportPerDay
     *
     * @return string
     */
    public function projectedHoursLeftInMonth($reportPerDay)
    {
        return array_key_exists('projectedHoursLeftInMonth', $reportPerDay)
            ? $reportPerDay['projectedHoursLeftInMonth'] : 'N/A';
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
