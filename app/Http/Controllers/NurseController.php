<?php

namespace App\Http\Controllers;

use App\Activity;
use App\Billing\NurseMonthlyBillGenerator;
use App\Mail\NurseInvoiceMailer;
use App\Nurse;
use App\PageTimer;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Yajra\Datatables\Facades\Datatables;

class NurseController extends Controller
{
    public function makeInvoice()
    {

        $nurses = (new \App\Nurse())->activeNursesForUI();

        return view('billing.nurse.create',
            [
                'nurses' => $nurses->sort(),
            ]
        );

    }

    public function generateInvoice(Request $request)
    {

        $input = $request->input();

        $nurses = $request->input('nurses');

        $addTime = $request->input('manual_time')
            ? $request->input('manual_time')
            : 0;

        $addNotes = $request->input('manual_time_notes')
            ? $request->input('manual_time_notes')
            : '';

        $variablePay = isset($input['alternative_pay']);

        if ($request->input('submit') == 'download') {

            $links = [];

            $startDate = Carbon::parse($request->input('start_date'));
            $endDate = Carbon::parse($request->input('end_date'));

            foreach ($nurses as $nurse) {

                $nurse = Nurse::where('user_id', $nurse)->first();

                $generator = (new NurseMonthlyBillGenerator($nurse, $startDate, $endDate, $variablePay, $addTime,
                    $addNotes))
//                    ->formatItemizedActivities();
                    ->handle();

                $data[] = $generator;

                $links[$nurse->user_id]['link'] = $generator['link'];
                $links[$nurse->user_id]['name'] = $generator['name'];

            }

            return view('billing.nurse.list',
                [
                    'invoices' => $links,
                    'data'     => $data,
                    'month'    => Carbon::parse($startDate)->format('F'),
                ]
            );
        }
    }

    public function sendInvoice(Request $request)
    {

        $invoices = (array)json_decode($request->input('links'));
        $month = $request->input('month');

        foreach ($invoices as $key => $value) {

            $value = (array)$value;

            $user = User::find($key);

            Mail::to($user)->send(new NurseInvoiceMailer($key, $value['link'], $month));

        }

        return redirect()->route('admin.reports.nurse.invoice')->with(['success' => 'yes']);
    }

    public function makeDailyReport()
    {

        return view('admin.reports.nursedaily');

    }

    public function dailyReport()
    {

        $nurse_users = User::ofType('care-center')->where('access_disabled', 0)->get();

        $nurses = [];

        $i = 0;

        foreach ($nurse_users as $nurse) {

            $nurses[$i]['id'] = $nurse;
            $nurses[$i]['name'] = $nurse->fullName;

            $last_activity_date = DB::table('lv_page_timer')
                ->select(DB::raw('max(`actual_end_time`) as last_activity'))
                ->where('provider_id', $nurse->id)
                ->get();

            if ($last_activity_date[0]->last_activity == null) {
                $nurses[$i]['Time Since Last Activity'] = 'N/A';
            } else {
                $nurses[$i]['Time Since Last Activity'] = Carbon::parse($last_activity_date[0]->last_activity)->diffForHumans();

            }

            $nurses[$i]['# Scheduled Calls Today'] = $nurse->nurseInfo->countScheduledCallsForToday();
            $nurses[$i]['# Completed Calls Today'] = $nurse->nurseInfo->countCompletedCallsForToday();
            $nurses[$i]['# Successful Calls Today'] = $nurse->nurseInfo->countSuccessfulCallsMadeToday();

            $activity_time = Activity::
            where('provider_id', $nurse->id)
                ->createdToday()
                ->sum('duration');

            $H1 = floor($activity_time / 3600);
            $m1 = ($activity_time / 60) % 60;
            $s1 = $activity_time % 60;
            $activity_time_formatted = sprintf("%02d:%02d:%02d", $H1, $m1, $s1);

            $system_time = PageTimer::where('provider_id', $nurse->id)
                ->createdToday('updated_at')
                ->sum('billable_duration');

            $system_time_formatted = secondsToHMS($system_time);

            $nurses[$i]['CCM Mins Today'] = $activity_time_formatted;
            $nurses[$i]['Total Mins Today'] = $system_time_formatted;

            $carbon_now = Carbon::now();

            $nurses[$i]['lessThan20MinsAgo'] = false;

            if ($last_activity_date == null) {

                $nurses[$i]['last_activity'] = 'N/A';

            } else {

                $carbon_last_act = Carbon::parse($last_activity_date[0]->last_activity);
                $nurses[$i]['last_activity'] = $carbon_last_act->toDateTimeString();

                $diff = $carbon_now->diffInSeconds($carbon_last_act);

                if ($diff <= 1200 && $nurses[$i]['Time Since Last Activity'] != 'N/A') {
                    $nurses[$i]['lessThan20MinsAgo'] = true;
                }
            }

            if ($nurses[$i]['Time Since Last Activity'] == 'N/A') {
                unset($nurses[$i]);
            }

            $i++;

        }

        $nurses = collect($nurses);
        $nurses->sortBy('last_activity');

        return Datatables::collection($nurses)->make(true);

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

        $input = $request->input();

        if (isset($input['next'])) {

            $dayCounter = Carbon::parse($input['next'])->firstOfMonth()->startOfDay();
            $last = Carbon::parse($input['next'])->lastOfMonth()->endOfDay();

        } elseif (isset($input['previous'])) {

            $dayCounter = Carbon::parse($input['previous'])->firstOfMonth()->startOfDay();
            $last = Carbon::parse($input['previous'])->lastOfMonth()->endOfDay();

        } else {

            $dayCounter = Carbon::now()->firstOfMonth()->startOfDay();
            $last = Carbon::now()->lastOfMonth()->endOfDay();

        }

        $nurses = User::ofType('care-center')->where('access_disabled', 0)->get();
        $data = [];


        while ($dayCounter->lte($last)) {

            foreach ($nurses as $nurse) {

                if (!$nurse->nurseInfo) {
                    continue;
                }

                $countScheduled = $nurse->nurseInfo->countScheduledCallsFor($dayCounter);

                $countMade = $nurse->nurseInfo->countCompletedCallsFor($dayCounter);

                $formattedDate = $dayCounter->format('m/d Y');

                $name = $nurse->first_name[0] . '. ' . $nurse->last_name;

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

        return view('admin.reports.allocation', [
            'data'  => $data,
            'month' => Carbon::parse($last),
        ]);

    }

}
