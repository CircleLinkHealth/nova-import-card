<?php namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\PageTimer;
use App\User;
use Auth;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
use Excel;
use Illuminate\Http\Request;

class NurseTimeReportController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        dd('This module has been disabled because the server blows up. If you need to run this, let the developers know on Slack. Thanks.');

        if (!Auth::user()->can('report-nurse-time-view')) {
            //abort(403);
        }

        // get all users with paused ccm_status
        $users = User::with('meta')
            ->with('roles')
            ->whereHas('roles', function ($q) {
                $q->where(function ($query) {
                    $query->orWhere('name', 'care-center');
                    $query->orWhere('name', 'no-ccm-care-center');
                });
            })
            ->get();

        $i = 0;

        // header
        $reportColumns = ['date'];
        foreach ($users as $user) {
            $reportColumns[] = $user->display_name;
        }

        // get array of dates
        $startDate = Carbon::now()->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        // if form submitted dates, override here
        $showAllTimes = false;
        if ($request->input('showAllTimes') == 'checked') {
            $showAllTimes = 'checked';
        }
        if ($request->input('start_date')) {
            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        }
        if ($request->input('end_date')) {
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
        }

        // to exclude the end date (so you just get dates between start and end date):
        // $b->modify('-1 day');

        $period = new DatePeriod($startDate, new DateInterval('P1D'), $endDate);

        $sheetRows = []; // so we can reverse after

        $userTotals = ['TOTAL:'];
        foreach ($period as $dt) {
            //echo $dt->format('Y-m-d') .'<br />';

            $rowUserValues = [$dt->format('Y-m-d')];
            foreach ($users as $user) {
                // get total activity time
                $pageTime = PageTimer::whereBetween('start_time', [
                    $dt->format('Y-m-d') . ' 00:00:01',
                    $dt->format('Y-m-d') . ' 23:59:59',
                ])
                    ->where('provider_id', $user->id);
                // toggle whether to show total times
                if (!$showAllTimes) {
                    $pageTime = $pageTime->where('activity_type', '!=', '');
                }
                $pageTime = $pageTime->sum('duration');

                $total = number_format((float)($pageTime / 60), 2, '.', '');
                $rowUserValues[] = $total;
                if (!isset($userTotals[$user->id])) {
                    $userTotals[$user->id] = 0;
                }
                $userTotals[$user->id] = $userTotals[$user->id] + $total;
            }

            $sheetRows[] = $rowUserValues;
        }

        $sheetRows = array_reverse($sheetRows);

        // $reportRows
        $reportRows = [];
        foreach ($sheetRows as $sheetRow) {
            $reportRows[] = $sheetRow;
        }
        $reportRows[] = $userTotals;

        // display view
        return view('admin.reports.nurseTimeReport.index', [
            'reportColumns' => $reportColumns,
            'reportRows'    => $reportRows,
            'startDate'     => $startDate,
            'endDate'       => $endDate,
            'showAllTimes'  => $showAllTimes,
        ]);
    }

    /**
     * export
     */

    public function exportxls(Request $request)
    {
        // get array of dates
        $startDate = new DateTime('first day of this month');
        $endDate = new DateTime(date('Y-m-d'));

        // if form submitted dates, override here
        $showAllTimes = false;
        if ($request->input('showAllTimes')) {
            $showAllTimes = 'checked';
        }
        if ($request->input('start_date')) {
            $startDate = new DateTime($request->input('start_date') . ' 00:00:01');
        }
        if ($request->input('end_date')) {
            $endDate = new DateTime($request->input('end_date') . ' 23:59:59');
        }

        $period = new DatePeriod($startDate, new DateInterval('P1D'), $endDate);

        $users = User::with('meta')
            ->with('roles')
            ->whereHas('roles', function ($q) {
                $q->where(function ($query) {
                    $query->orWhere('name', 'care-center');
                    $query->orWhere('name', 'no-ccm-care-center');
                });
            })
            ->get();

        $date = date('Y-m-d H:i:s');

        Excel::create('CLH-Report-' . $date, function ($excel) use
            (
            $date,
            $users,
            $period,
            $showAllTimes
        ) {

            // Set the title
            $excel->setTitle('CLH Report T3');

            // Chain the setters
            $excel->setCreator('CLH System')
                ->setCompany('CircleLink Health');

            // Call them separately
            $excel->setDescription('CLH Report T3');

            // Our first sheet
            $excel->sheet('Sheet 1', function ($sheet) use
                (
                $users,
                $period,
                $showAllTimes
            ) {
                $sheet->protect('clhpa$$word', function (\PHPExcel_Worksheet_Protection $protection) {
                    $protection->setSort(true);
                });
                $i = 0;
                // header
                $userColumns = ['date'];
                foreach ($users as $user) {
                    $userColumns[] = $user->display_name;
                }
                $sheet->appendRow($userColumns);

                $sheetRows = []; // so we can reverse after

                foreach ($period as $dt) {
                    $rowUserValues = [$dt->format('Y-m-d')];
                    foreach ($users as $user) {
                        // get total activity time
                        $pageTime = PageTimer::whereBetween('start_time', [
                            $dt->format('Y-m-d') . ' 00:00:01',
                            $dt->format('Y-m-d') . ' 23:59:59',
                        ])
                            ->where('provider_id', $user->id);
                        // toggle whether to show total times
                        if (!$showAllTimes) {
                            $pageTime = $pageTime->where('activity_type', '!=', '');
                        }
                        $pageTime = $pageTime->sum('duration');

                        $rowUserValues[] = number_format((float)($pageTime / 60), 2, '.', '');
                    }

                    $sheetRows[] = $rowUserValues;
                }

                $sheetRows = array_reverse($sheetRows);

                foreach ($sheetRows as $sheetRow) {
                    $sheet->appendRow($sheetRow);
                }
            });
        })->export('xls');
    }
}
