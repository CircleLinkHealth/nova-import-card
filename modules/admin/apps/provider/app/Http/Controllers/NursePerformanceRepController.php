<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Services\NursesPerformanceReportService;
use Carbon\Carbon;
use CircleLinkHealth\Core\Exports\FromArray;
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
            ? $reportPerDay['uniquePatientsAssignedForMonth']
            : 'N/A';
    }

    /**
     * @param $reportPerDay
     *
     * @return string
     */
    public function caseLoadComplete($reportPerDay)
    {
        return array_key_exists('caseLoadComplete', $reportPerDay)
            ? $reportPerDay['caseLoadComplete']
            : 'N/A';
    }

    /**
     * @param $reportPerDay
     *
     * @return string
     */
    public function caseLoadNeededToComplete($reportPerDay)
    {
        return array_key_exists('caseLoadNeededToComplete', $reportPerDay)
            ? $reportPerDay['caseLoadNeededToComplete']
            : 'N/A';
    }

    /**
     * @param $reportPerDay
     *
     * @return string
     */
    public function completionRate($reportPerDay)
    {
        return array_key_exists('completionRate', $reportPerDay)
            ? (0 == $reportPerDay['committedHours'] ? 'N/A' : $reportPerDay['completionRate'])
            : 'N/A';
    }

    /**
     * @param $reportPerDay
     *
     * @return string
     */
    public function efficiencyIndex($reportPerDay)
    {
        return array_key_exists('efficiencyIndex', $reportPerDay)
            ? (0 == $reportPerDay['committedHours'] ? 'N/A' : $reportPerDay['efficiencyIndex'])
            : 'N/A';
    }

    /**
     * Gets input date and collects days from that date back to beginning of that week.
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
            ? $reportPerDay['hoursCommittedRestOfMonth']
            : 'N/A';
    }

    /**
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
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function nurseMetricsPerformanceData(Request $request)
    {
        return datatables()->collection($this->getNursePerformanceData($request))->make(true);
    }

    /**
     * @throws \Exception
     *
     * @return
     */
    public function nurseMetricsPerformanceExcel(Request $request)
    {
        $input = $request->input();

        if (isset($input['start_date'], $input['end_date'])) {
            $startDate = Carbon::parse($input['start_date']);
            $endDate   = Carbon::parse($input['end_date']);
        } else {
            $startDate = Carbon::parse($input['start_date']);
            $endDate   = Carbon::parse($input['end_date']);
        }

        $days   = $this->getDaysBetweenPeriodRange($startDate, $endDate);
        $nurses = $this->service->manipulateData($days);

        $filename = "Nurse Performance Report - {$startDate->toDateString()} to {$endDate->toDateString()}.xlsx";

        $data = collect($this->nursesDataForView($nurses))->sortBy(function ($item) {
            return $item['Day'].'-'.$item['Name'];
        });

        return (new FromArray($filename, $data->toArray()))->download($filename);
    }

    /**
     * @return array
     */
    public function nursesDataForView(Collection $nurses)
    {
        $nurseDailyData = [];
        $n              = 0;
        foreach ($nurses as $name => $report) {
            foreach ($report as $day => $reportPerDay) {
                $nurseDailyData[$n] = [
                    'Day'            => Carbon::parse($day)->copy()->format('j'),
                    'Name'           => $reportPerDay['nurse_full_name'],
                    'Assigned Calls' => $reportPerDay['scheduledCalls']
                        ?: '0',
                    'Actual Calls' => $reportPerDay['actualCalls']
                        ?: '0', // this completed calls in UI
                    'Successful Calls' => $reportPerDay['successful']
                        ?: '0',
                    'Unsuccessful Calls' => $reportPerDay['unsuccessful']
                        ?: '0',
                    'Avg CCM Time Per Successful Patient' => $reportPerDay['avgCCMTimePerPatient']
                        ?: '0',
                    'Avg Completion Time Per Patient' => $reportPerDay['avgCompletionTime']
                        ?: '0',
                    'Actual Hrs Worked' => $reportPerDay['actualHours']
                        ?: '0',
                    'Committed Hrs' => $reportPerDay['committedHours']
                        ?: '0',
                    'Attendance/Calls Completion Rate' => $this->completionRate($reportPerDay)
                        ?: '0.00',
                    'Efficiency Index' => $this->efficiencyIndex($reportPerDay)
                        ?: '0.00',
                    'Est. Hrs to Complete Case Load' => $this->caseLoadNeededToComplete($reportPerDay)
                        ?: '0.0',
                    'Projected Hrs. Left In Month' => $this->projectedHoursLeftInMonth($reportPerDay)
                        ?: '0.00',
                    'Hrs Committed Rest of Month' => $this->hoursCommittedRestOfMonth($reportPerDay)
                        ?: '0',
                    'Hrs Deficit or Surplus' => $this->surplusShortfallHours($reportPerDay)
                        ?: '0',
                    'Case Load' => $this->caseLoad($reportPerDay)
                        ?: '0',
                    'Incomplete Patients' => $reportPerDay['incompletePatients']
                        ?: '0',
                    '% Case Load Complete' => $this->caseLoadComplete($reportPerDay)
                        ?: '0.00',
                ];
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
            ? $reportPerDay['projectedHoursLeftInMonth']
            : 'N/A';
    }

    /**
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
            ? $reportPerDay['surplusShortfallHours']
            : 'N/A';
    }

    /**
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
