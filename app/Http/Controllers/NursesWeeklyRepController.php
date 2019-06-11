<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Services\NursesAndStatesDailyReportService;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class NursesWeeklyRepController extends Controller
{
    /**
     * @var NursesAndStatesDailyReportService
     */
    private $service;

    /**
     * NursesWeeklyRepController constructor.
     *
     * @param NursesAndStatesDailyReportService $service
     */
    public function __construct(NursesAndStatesDailyReportService $service)
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
     * @param Carbon $date
     * @param Carbon $startOfWeek
     *
     * @return array
     */
    public function getDaysFromDateTillStartOfWeek(Carbon $date, Carbon $startOfWeek)
    {
        $days = [];

        $upToDayOfWeek = carbonToClhDayOfWeek($date->dayOfWeek);
        for ($i = 0; $i < $upToDayOfWeek; ++$i) {
            $days[] = $startOfWeek->copy()->addDay($i);
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
     * @param Collection $nurses
     *
     * @return array
     */
    public function getNursesDailyData(Collection $nurses)
    {
        $data = $nurses->forget('totals');
        //@todo:one level of indendetion
        $nurseDailyData = [];
        $n              = 0;
        foreach ($data as $name => $report) {
            foreach ($report as $reportPerDay) {
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

        return $nurseDailyData;
    }

    /**
     * @param Collection $nurses
     *
     * @return array
     */
    //this is not used yet
//    public function getNursesDailyTotals(Collection $nurses)
//    {
//        $nurseDailyTotals = [];
//        $n                = 0;
//        $totals           = $nurses->only('totals');
//        foreach ($totals as $total => $totalsPerDay) {
//            foreach ($totalsPerDay as $totalsForDay) {
//                $nurseDailyTotals[$n]['scheduledCallsSum']         = $totalsForDay['scheduledCallsSum'];
//                $nurseDailyTotals[$n]['actualCallsSum']            = $totalsForDay['actualCallsSum'];
//                $nurseDailyTotals[$n]['successfulCallsSum']        = $totalsForDay['successfulCallsSum'];
//                $nurseDailyTotals[$n]['unsuccessfulCallsSum']      = $totalsForDay['unsuccessfulCallsSum'];
//                $nurseDailyTotals[$n]['actualHoursSum']            = $totalsForDay['actualHoursSum'];
//                $nurseDailyTotals[$n]['committedHoursSum']         = $totalsForDay['committedHoursSum'];
//                $nurseDailyTotals[$n]['completionRate']            = $totalsForDay->has('completionRate') ? $totalsForDay['completionRate'] : 'N/A';
//                $nurseDailyTotals[$n]['efficiencyIndex']           = $totalsForDay->has('efficiencyIndex') ? $totalsForDay['efficiencyIndex'] : 'N/A';
//                $nurseDailyTotals[$n]['caseLoadNeededToComplete']  = $totalsForDay->has('caseLoadNeededToComplete') ? $totalsForDay['caseLoadNeededToComplete'] : 'N/A';
//                $nurseDailyTotals[$n]['hoursCommittedRestOfMonth'] = $totalsForDay->has('hoursCommittedRestOfMonth') ? $totalsForDay['hoursCommittedRestOfMonth'] : 'N/A';
//                $nurseDailyTotals[$n]['surplusShortfallHours']     = $totalsForDay->has('surplusShortfallHours') ? $totalsForDay['surplusShortfallHours'] : 'N/A';
//                $nurseDailyTotals[$n]['caseLoadComplete']          = $totalsForDay->has('caseLoadComplete') ? $totalsForDay['caseLoadComplete'] : 'N/A';
//
//                ++$n;
//            }
//        }
//
//        return $nurseDailyTotals;
//    }

    /**
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return Collection
     */
    public function getNurseWeeklyData(Request $request)
    {
        $input       = $request->input();
        $date        = Carbon::parse($input['date']);
        $startOfWeek = $this->getStartOfWeek($date);
        $days        = $this->getDaysFromDateTillStartOfWeek($date, $startOfWeek);

        //data are returned in 2 arrays. {Data} and the {Totals of data}.
        $nurses = $this->service->manipulateData($days);

        $nurseDailyData = $this->getNursesDailyData($nurses);
//        $nurseDailyTotals = $this->getNursesDailyTotals($nurses);

        return collect($nurseDailyData);
    }

    /**
     * @param Carbon $date
     *
     * @return Carbon
     */
    public function getStartOfWeek(Carbon $date)
    {
        return $date->copy()->startOfWeek();
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
    public function nurseMetricsWeeklyDashboard(Request $request)
    {
        $yesterdayDate = Carbon::yesterday()->startOfDay();
        $limitDate     = $this->service->getLimitDate();
        $date          = $this->setDate($request, $yesterdayDate);
        $startOfWeek   = $this->getStartOfWeek($date);
        $days          = $this->getDaysFromDateTillStartOfWeek($date, $startOfWeek);

        return view(
            'admin.reports.nurseWeekly',
            compact('date', 'yesterdayDate', 'limitDate', 'startOfWeek', 'days')
        );
    }

    /**
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function nurseMetricsWeeklyData(Request $request)
    {
        return datatables()->collection(collect($this->getNurseWeeklyData($request)))->make(true);
    }

    /**
     * @param Request $request
     * @param Carbon  $yesterdayDate
     *
     * @return Carbon|RedirectResponse
     */
    public function setDate(Request $request, Carbon $yesterdayDate)
    {
        if ($request->has('date')) {
            $requestDate = new Carbon($request['date']);
            $date        = $requestDate->copy();
        } else {//how to avoid else here?
            $date = $yesterdayDate->copy();
        }

        if ($date->gte(today()->startOfDay())) {
            $messages['errors'][] = 'Please input a past date';

            return redirect()->back()->withErrors($messages);
        }

        return $date;
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
}
