<?php

namespace App\Http\Controllers;

use App\Activity;
use App\Billing\NurseMonthlyBillGenerator;
use App\Call;
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

            foreach ($nurses as $nurse) {

                $nurse = Nurse::where('user_id', $nurse)->first();
                $startDate = Carbon::parse($request->input('start_date'));
                $endDate = Carbon::parse($request->input('end_date'));

                $generator = (new NurseMonthlyBillGenerator($nurse, $startDate, $endDate, $variablePay, $addTime,
                    $addNotes))
//                    ->formatItemizedActivities();
                    ->handle();


                $data[] = $generator;

                $links[$generator['name']] = $generator['link'];

            }

            return view('billing.nurse.list',
                [
                    'invoices' => $links,
                    'data'     => $data,
                ]
            );
        }
    }

    public function makeDailyReport()
    {

        return view('admin.reports.nursedaily');

    }

    public function dailyReport()
    {

        $nurse_users = User::ofType('care-center')->get();

        $nurses = [];

        $i = 0;

        foreach ($nurse_users as $nurse) {

            $nurses[$i]['id'] = $nurse;
            $nurses[$i]['name'] = $nurse->fullName;

            $last_activity_date = DB::table('lv_page_timer')
                ->select(DB::raw('max(`end_time`) as last_activity'))
                ->where('provider_id', $nurse->id)
                ->get();

            if ($last_activity_date[0]->last_activity == null) {
                $nurses[$i]['Time Since Last Activity'] = 'N/A';
            } else {
                $nurses[$i]['Time Since Last Activity'] = Carbon::parse($last_activity_date[0]->last_activity)->diffForHumans();

            }

            $nurses[$i]['# Calls Today'] =
                Call::where('outbound_cpm_id', $nurse->id)
                    ->where(function ($q) {
                        $q->where('updated_at', '>=', Carbon::now()->startOfDay())
                            ->where('updated_at', '<=', Carbon::now()->endOfDay());
                    })
                    ->where(function ($k) {
                        $k->where('status', 'reached')
                            ->orWhere('status', '');
                    })
                    ->count();

            $nurses[$i]['# Successful Calls Today'] =
                Call::where('outbound_cpm_id', $nurse->id)
                    ->where(function ($j) {
                        $j->where('updated_at', '>=', Carbon::now()->startOfDay())
                            ->where('updated_at', '<=', Carbon::now()->endOfDay());
                    })
                    ->where('status', 'reached')
                    ->count();

//        $nurses[$nurse->fullName]['# Scheduled Calls Today'] =
//            \App\Call::where('outbound_cpm_id', $nurse->id)
//                ->where(function ($q){
//                    $q->where('updated_at', '>=' , Carbon::now()->startOfDay())
//                        ->where('updated_at', '<=' , Carbon::now()->endOfDay());
//                })
//                ->where('status', 'scheduled')
//                ->count();

            $activity_time = Activity::createdBy($nurse)
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

        debug($nurses);

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

    public function sendInvoice(Request $request)
    {

        $data = json_decode($request->input('data'), true);

        foreach ($data as $nurse_array) {

            $fileName = $nurse_array['link'];
            $nurse = Nurse::find($nurse_array['id']);
            $date_start = $nurse_array['date_start'];
            $date_end = $nurse_array['date_end'];

            Mail::send('billing.nurse.mail', $nurse_array['email_body'], function ($m) use
            (
                $nurse,
                $fileName,
                $date_start,
                $date_end
            ) {

                $m->from('billing@circlelinkhealth.com', 'CircleLink Health');

                $m->attach(storage_path("download/$fileName"));

                $m->to($nurse->user->email, $nurse->user->fullName)
                    ->subject('New Invoice from CircleLink Health [' . $date_start . ' to ' . $date_end . ']');

                $m->cc('raph@circlelinkhealth.com');

            });

        }

        return redirect()->route('admin.reports.nurse.invoice');
    }

}
