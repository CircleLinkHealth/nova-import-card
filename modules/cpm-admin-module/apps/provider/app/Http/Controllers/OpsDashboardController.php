<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Charts\OpsChart;
use Carbon\Carbon;
use CircleLinkHealth\Core\Exports\FromArray;
use CircleLinkHealth\Customer\Entities\SaasAccount;
use Illuminate\Http\Request;

class OpsDashboardController extends Controller
{
    public function dailyCsv(Request $request)
    {
        if ($request->has('date')) {
            $requestDate = Carbon::parse($request['date']);
            $date        = $requestDate->copy();
        } else {
            //if the admin loads the page today, we need to display last night's report
            $date = Carbon::yesterday();
        }
        //there are no compatible reports in the cloud before this day
        $noReportDates = Carbon::parse('5 August 2018');
        //for older reports that dont have dateGenerated
        $dateGenerated = null;

        $json = optional(SaasAccount::whereSlug('circlelink-health')
            ->first()
            ->getMedia("ops-daily-report-{$date->toDateString()}.json")
            ->sortByDesc('id')
            ->first())
            ->getFile();

        //first check if we have a valid file
        if ( ! $json || $date <= $noReportDates) {
            $hoursBehind = 'N/A';
            $rows        = [];
        } else {
            //then check if it's in json format
            if ( ! is_json($json)) {
                throw new \Exception('File retrieved is not in json format.', 500);
            }

            $data        = json_decode($json, true);
            $hoursBehind = $data['hoursBehind'];
            $rows        = $data['rows'];
            if (array_key_exists('dateGenerated', $data)) {
                $dateGenerated = Carbon::parse($data['dateGenerated']);
            }
        }

        $fileName = "CLH-Ops-CSV-Report-{$date->format('Y-m-d-H:i:s')}.xls";

        $reportRows = collect();

        $reportRows->push(["Ops Report for: {$date->copy()->toDateString()}"]);
        $reportRows->push(["HoursBehind: {$hoursBehind}"]);

        //empty row
        $reportRows->push(['']);

        $reportRows->push([
            'Active Accounts',
            '0 mins',
            '0-5',
            '5-10',
            '10-15',
            '15-20',
            '20+',
            '20+ BHI',
            'Total',
            'Prior Day Totals',
            'Added',
            'Unreachable',
            'Paused',
            'Withdrawn',
            'Delta',
            'G0506 To Enroll',
        ]);

        foreach ($rows as $key => $value) {
            $reportRows->push(
                [
                    $key,
                    (string) ($value['0 mins'] ?? 0),
                    (string) ($value['0-5'] ?? 0),
                    (string) ($value['5-10'] ?? 0),
                    (string) ($value['10-15'] ?? 0),
                    (string) ($value['15-20'] ?? 0),
                    (string) ($value['20+'] ?? 0),
                    (string) ($value['20+ BHI'] ?? 0),
                    (string) ($value['Total'] ?? 0),
                    (string) ($value['Prior Day totals'] ?? 0),
                    (string) ($value['Added'] ?? 0),
                    (string) ('-'.$value['Unreachable'] ?? 0),
                    (string) ('-'.$value['Paused'] ?? 0),
                    (string) ('-'.$value['Withdrawn'] ?? 0),
                    (string) ($value['Delta'] ?? 0),
                    (string) ($value['G0506 To Enroll'] ?? 0),
                ]
            );
        }

        return (new FromArray($fileName, $reportRows->all(), []))->download($fileName);
    }

    public function getBillingChurn(Request $request)
    {
        //Page times out. We should implement solution similar to OpsDashbboard (queue job save data on S3), or SQL view, or Nova page with metrics
        return 'Page is unavailable. To be fixed in CPM-1717';
        //check git for previous version
    }

    public function getMonths(Carbon $date, $number)
    {
        $months = [];

        for ($x = $number; $x > 0; --$x) {
            $months[] = $date->copy()->subMonth($x)->startOfMonth();
        }

        return collect($months);
    }

    /**
     * Gets Patient Counts for table: CarePlan Manager Patient Totals,
     * for today, for specific day.
     *
     * @throws \Exception
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $maxDate = Carbon::today()->subDay(1);

        if ($request->has('date')) {
            $requestDate = new Carbon($request['date']);
            $date        = $requestDate->copy();
        } else {
            //if the admin loads the page today, we need to display last night's report
            $date = $maxDate->copy();
        }
        //there are no compatible reports in the cloud before this day
        $noReportDates = Carbon::parse('5 August 2018');
        //for older reports that dont have dateGenerated
        $dateGenerated = null;

        $json = optional(SaasAccount::whereSlug('circlelink-health')
            ->first()
            ->getMedia("ops-daily-report-{$date->toDateString()}.json")
            ->sortByDesc('id')
            ->first())
            ->getFile();

        //first check if we have a valid file
        if ( ! $json || $date <= $noReportDates) {
            $hoursBehind = 'N/A';
            $rows        = [];
        } else {
            //then check if it's in json format
            if ( ! is_json($json)) {
                throw new \Exception('File retrieved is not in json format.', 500);
            }

            $data        = json_decode($json, true);
            $hoursBehind = $data['hoursBehind'];
            $rows        = $data['rows'];
            if (array_key_exists('dateGenerated', $data)) {
                $dateGenerated = Carbon::parse($data['dateGenerated']);
            }
        }

        return view('admin.opsDashboard.daily', compact([
            'date',
            'maxDate',
            'hoursBehind',
            'rows',
            'dateGenerated',
        ]));
    }

    public function opsGraph()
    {
        return view('charts.ops')->with('chart', OpsChart::clhGrowthChart());
    }
}
