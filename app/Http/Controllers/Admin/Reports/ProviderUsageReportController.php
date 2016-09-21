<?php namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\PageTimer;
use App\Program;
use App\User;
use Auth;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
use Excel;
use Illuminate\Http\Request;

class ProviderUsageReportController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        // get array of dates
        $startDate = new DateTime('first day of this month');
        $endDate = new DateTime(date('Y-m-d'));

        // if form submitted dates, override here
        $showAllTimes = false;
        if ($request->all('showAllTimes') == 'checked') {
            $showAllTimes = 'checked';
        }
        if ($request->all('start_date')) {
            $startDate = new DateTime($request->input('start_date') . ' 00:00:01');
        }
        if ($request->all('end_date')) {
            $endDate = new DateTime($request->input('end_date') . ' 23:59:59');
        }

        //
        $programStats = [];

        // get all program
        $programs = Program::get()->lists('display_name', 'blog_id')->all();

        $period = new DatePeriod($startDate, new DateInterval('P1D'), $endDate);

        $sheetRows = []; // so we can reverse after

        $userTotals = ['TOTAL:'];
        foreach ($period as $dt) {

        }

        // get stats for each program
        foreach ($programs as $programId => $programName) {
            $programStats[$programName] = [];
            // get users
            $patientIds = User::
            whereHas('programs', function ($q) use
            (
                $programId
            ) {
                $q->whereIn('program_id', [$programId]);
            })
                ->whereHas('roles', function ($q) {
                    $q->whereIn('name', [
                        'provider',
                        'med_assistant'
                    ]);
                })
                ->pluck('ID')->toArray();

            $programStats[$programName]['number_of_office_users'] = count($patientIds);


            $programStats[$programName]['dates'] = []; // array of dates
            // get all pagetimes for users, per day:
            $pagetimes = PageTimer::
            whereHas('logger', function ($q) use
            (
                $patientIds
            ) {
                $q->whereIn('ID', $patientIds);
            })
                ->whereBetween('start_time', [
                    $startDate,
                    $endDate
                ])
                //->limit(10)
                ->get(); // ->sum('duration')
            foreach ($period as $dt) {
                $pagetimesForDate = 0;
                if ($pagetimes->count() > 0) {
                    $pagetimesForDate = $pagetimes->filter(function ($item) use
                    (
                        $dt
                    ) {
                        return (data_get($item, 'start_time') > $dt->format('Y-m-d') . ' 00:00:01') && (data_get($item,
                                'start_time') < $dt->format('Y-m-d') . ' 23:59:59');
                    })->count();
                }
                $programStats[$programName]['dates'][$dt->format('Y-m-d')] = [];
                $programStats[$programName]['dates'][$dt->format('Y-m-d')]['pageviews'] = $pagetimesForDate;
            }

            //$programStats[$programName]['number_of_pageviews'] = $pagetimes->count();
        }
        //dd($programStats);

        $worksheets = $programStats;

        $date = Carbon::now()->startOfMonth();

        Excel::create('CLH-Provider_Usage-Report-' . $date, function ($excel) use
        (
            $date,
            $worksheets
        ) {

            // Set the title
            $excel->setTitle('CLH Call Report - ' . $date);

            // Chain the setters
            $excel->setCreator('CLH System')
                ->setCompany('CircleLink Health');

            // Call them separately
            $excel->setDescription('CLH Call Report - ' . $date);

            // headers
            $headers = [
                'Date',
                'Pageviews'
            ];

            // sheet for each program
            foreach ($worksheets as $worksheetName => $worksheetData) {
                $excel->sheet("{$worksheetName}", function ($sheet) use
                (
                    $worksheetData,
                    $headers
                ) {
                    $sheet->appendRow($headers);
                    foreach ($worksheetData['dates'] as $date => $dateData) {
                        $sheet->appendRow([
                            $date,
                            $dateData['pageviews']
                        ]);
                    }
                });
            }

        })->export('xls');
    }

}
