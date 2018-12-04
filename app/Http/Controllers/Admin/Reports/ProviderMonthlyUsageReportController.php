<?php namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\PageTimer;
use App\Practice;
use App\User;
use Excel;
use Illuminate\Http\Request;

class ProviderMonthlyUsageReportController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $startDate = '2016-01-01 00:00:01';
        $endDate = '2016-10-31 23:59:59';
        // months array
        $monthDates = [
            '2016-01-01' => '2016-01-31',
            '2016-02-01' => '2016-02-29',
            '2016-03-01' => '2016-03-31',
            '2016-04-01' => '2016-04-30',
            '2016-05-01' => '2016-05-31',
            '2016-06-01' => '2016-06-30',
            '2016-07-01' => '2016-07-31',
            '2016-08-01' => '2016-08-31',
            '2016-09-01' => '2016-09-30',
            '2016-10-01' => '2016-10-31',
            '2016-11-01' => '2016-11-30',
            '2016-12-01' => '2016-12-31',
        ];

        //
        $programStats = [];

        $program = 'nestor';
        if ($request->all('program')) {
            $program = $request->input('program');
        }

        // get all program
        $programs = Practice::where('name', '=', $program)->get()->pluck('display_name', 'id')->all();

        // get stats for each program
        foreach ($programs as $programId => $programName) {
            $programStats[$programName] = [];
            $programStats[$programName]['dates'] = []; // array of dates

            /***** OFFICE USERS *******/
            // get users
            $officeUserIds = User::
            whereHas('practices', function ($q) use (
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
                ->pluck('id')->toArray();

            $programStats[$programName]['number_of_office_users'] = count($officeUserIds);

            // get all pagetimes for users, per day:
            $pagetimes = PageTimer::
            whereHas('logger', function ($q) use (
                $officeUserIds
            ) {
                $q->whereIn('id', $officeUserIds);
            })
                ->whereBetween('start_time', [
                    $startDate,
                    $endDate
                ])
                //->limit(10)
                ->get(); // ->sum('duration')
            foreach ($monthDates as $monthDateStart => $monthDateEnd) {
                $programStats[$programName]['dates'][$monthDateStart . '-' . $monthDateEnd] = [];
                $pagetimesForDate = 0;
                if ($pagetimes->count() > 0) {
                    $pagetimesForDate = $pagetimes->filter(function ($item) use (
                        $monthDateStart,
                        $monthDateEnd
                    ) {
                        return (data_get($item, 'start_time') > $monthDateStart . ' 00:00:01') && (data_get(
                            $item,
                            'start_time'
                        ) < $monthDateEnd . ' 23:59:59');
                    })->count();
                }
                $programStats[$programName]['dates'][$monthDateStart . '-' . $monthDateEnd]['pageviews'] = $pagetimesForDate;
            }


            /***** CARE CENTER USERS *******/
            // get users
            $nurseUserIds = User::
            whereHas('practices', function ($q) use (
                $programId
            ) {
                $q->whereIn('program_id', [$programId]);
            })
                ->whereHas('roles', function ($q) {
                    $q->whereIn('name', [
                        'care-center'
                    ]);
                })
                ->pluck('id')->toArray();

            $programStats[$programName]['number_of_nurse_users'] = count($nurseUserIds);

            // get participants
            $participantUserIds = User::
            whereHas('practices', function ($q) use (
                $programId
            ) {
                $q->whereIn('program_id', [$programId]);
            })
                ->whereHas('roles', function ($q) {
                    $q->whereIn('name', [
                        'participant'
                    ]);
                })
                ->pluck('id')->toArray();

            $programStats[$programName]['number_of_participant_users'] = count($participantUserIds);

            // get all pagetimes for users, per day:
            $pagetimes = PageTimer::
            whereHas('logger', function ($q) use (
                $nurseUserIds
            ) {
                $q->whereIn('id', $nurseUserIds);
            })
                ->whereHas('patient', function ($q) use (
                    $participantUserIds
                ) {
                    $q->whereIn('id', $participantUserIds);
                })
                ->whereBetween('start_time', [
                    $startDate,
                    $endDate
                ])
                //->limit(10)
                ->get(); // ->sum('duration')
            foreach ($monthDates as $monthDateStart => $monthDateEnd) {
                $pagetimesForDate = 0;
                if ($pagetimes->count() > 0) {
                    $pagetimesForDate = $pagetimes->filter(function ($item) use (
                        $monthDateStart,
                        $monthDateEnd
                    ) {
                        return (data_get($item, 'start_time') > $monthDateStart . ' 00:00:01') && (data_get(
                            $item,
                            'start_time'
                        ) < $monthDateEnd . ' 23:59:59');
                    })->count();
                }
                $programStats[$programName]['dates'][$monthDateStart . '-' . $monthDateEnd]['nurse_pageviews'] = $pagetimesForDate;
            }


            //$programStats[$programName]['number_of_pageviews'] = $pagetimes->count();
        }

        $worksheets = $programStats;


        Excel::create('CLH-Provider-Usage-Report-2016-' . $program, function ($excel) use (
            $worksheets,
            $program
        ) {

            // Set the title
            $excel->setTitle('CLH Provider Monthly Usage Report-  2016 - ' . $program);

            // Chain the setters
            $excel->setCreator('CLH System')
                ->setCompany('CircleLink Health');

            // Call them separately
            $excel->setDescription('CLH Provider Monthly Usage Report - 2016 - ' . $program);

            // headers
            $headers = [
                'Date Range',
                'Office Pageviews',
                'Nurse Pageviews'
            ];

            // sheet for each program
            foreach ($worksheets as $worksheetName => $worksheetData) {
                $excel->sheet(substr($worksheetName, 0, 20), function ($sheet) use (
                    $worksheetData,
                    $headers
                ) {
                    $sheet->appendRow($headers);
                    foreach ($worksheetData['dates'] as $date => $dateData) {
                        $sheet->appendRow([
                            $date,
                            $dateData['pageviews'],
                            $dateData['nurse_pageviews']
                        ]);
                    }
                });
            }
        })->export('xls');
    }
}
