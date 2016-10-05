<?php

namespace App\Http\Controllers;

use App\Activity;
use App\Billing\NurseMonthlyBillGenerator;
use App\Call;
use App\NurseInfo;
use App\PageTimer;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use Yajra\Datatables\Facades\Datatables;

class NurseController extends Controller
{
    public function makeInvoice()
    {

        $nurses = (new \App\NurseInfo())->activeNursesForUI();

        return view('billing.nurse.create',
            [
                'nurses' => $nurses->sort(),
            ]
        );

    }

    public function generateInvoice(Request $request)
    {

        $nurses = $request->input('nurses');
        $addTime = $request->input('manual_time') ? $request->input('manual_time') : 0;
        $addNotes = $request->input('manual_time_notes') ? $request->input('manual_time_notes') : '';

        if($request->input('submit') == 'download') {

            $links = [];

            foreach ($nurses as $nurse) {

                $nurse = NurseInfo::where('user_id', $nurse)->first();
                $startDate = Carbon::parse($request->input('start_date'));
                $endDate = Carbon::parse($request->input('end_date'));

                $links[$nurse->user->fullName] = (new NurseMonthlyBillGenerator($nurse, $startDate, $endDate, $addTime, $addNotes))->handle();

            }

            return view('billing.nurse.list', ['invoices' => $links]);
        }

        if($request->input('submit') == 'download') {
            $messages = [];
            foreach ($nurses as $nurse) {

                $nurse = NurseInfo::where('user_id', $nurse)->first();
                $startDate = Carbon::parse($request->input('start_date'));
                $endDate = Carbon::parse($request->input('end_date'));
                
                $messages[] = (new NurseMonthlyBillGenerator($nurse, $startDate, $endDate, $addTime, $addNotes))->email();

            }

            return $messages;
        }
    }

    public function makeDailyReport(){

        return view('admin.reports.nursedaily');

    }

    public function dailyReport(){

        $nurse_ids = User::whereHas('roles', function ($q) {
            $q->where('name', '=', 'care-center');
        })->pluck('ID');

        $i = 0;
        $nurses = array();


        foreach ($nurse_ids as $nurse_id){

            $nurse = User::find($nurse_id);

            $nurses[$i]['id'] = $nurse_id;
            $nurses[$i]['name'] = $nurse->fullName;

            $last_activity_date = DB::table('lv_page_timer')->select(DB::raw('max(`end_time`) as last_activity'))->where('provider_id', $nurse_id)->get();

            if($last_activity_date[0]->last_activity == null){
                $nurses[$i]['Time Since Last Activity'] = 'N/A';
            } else {
                $nurses[$i]['Time Since Last Activity'] = Carbon::parse($last_activity_date[0]->last_activity)->diffForHumans();

            }

            $nurses[$i]['# Calls Today'] =
                Call::where('outbound_cpm_id', $nurse_id)
                    ->where(function ($q){
                        $q->where('updated_at', '>=' , Carbon::now()->startOfDay())
                            ->where('updated_at', '<=' , Carbon::now()->endOfDay());
                    })
                    ->where(function ($q){
                        $q->where('status', 'reached')
                            ->orWhere('status', '');
                    })
                    ->count();

            $nurses[$i]['# Successful Calls Today'] =
                Call::where('outbound_cpm_id', $nurse_id)
                    ->where(function ($q){
                        $q->where('updated_at', '>=' , Carbon::now()->startOfDay())
                            ->where('updated_at', '<=' , Carbon::now()->endOfDay());
                    })
                    ->where('status', 'reached')
                    ->count();

//        $nurses[$nurse->fullName]['# Scheduled Calls Today'] =
//            \App\Call::where('outbound_cpm_id', $nurse_id)
//                ->where(function ($q){
//                    $q->where('updated_at', '>=' , Carbon::now()->startOfDay())
//                        ->where('updated_at', '<=' , Carbon::now()->endOfDay());
//                })
//                ->where('status', 'scheduled')
//                ->count();

            $activity_time = Activity::where(function ($q) use ($nurse_id){
                $q->where('provider_id', $nurse_id)
                    ->orWhere('logger_id', $nurse_id);
            })
                ->where(function ($q){
                    $q->where('created_at', '>=' , Carbon::now()->startOfDay())
                        ->where('created_at', '<=' , Carbon::now()->endOfDay());
                })
                ->sum('duration');

            $H1 = floor($activity_time / 3600);
            $m1 = ($activity_time / 60) % 60;
            $s1 = $activity_time % 60;
            $activity_time_formatted = sprintf("%02d:%02d:%02d",$H1, $m1, $s1);

            $system_time = PageTimer::where('provider_id', $nurse_id)
                ->where(function ($q){
                    $q->where('updated_at', '>=' , Carbon::now()->startOfDay())
                        ->where('updated_at', '<=' , Carbon::now()->endOfDay());
                })
//				->whereNotNull('activity_type')
                ->sum('duration');

            $H2 = floor($system_time / 3600);
            $m2 = ($system_time / 60) % 60;
            $s2 = $system_time % 60;
            $system_time_formatted = sprintf("%02d:%02d:%02d",$H2, $m2, $s2);

            $nurses[$i]['CCM Mins Today'] = $activity_time_formatted;
            $nurses[$i]['Total Mins Today'] = $system_time_formatted;

            $carbon_now = Carbon::now();

            $nurses[$i]['lessThan20MinsAgo'] = false;

            if($last_activity_date == null){

                $nurses[$i]['last_activity'] = 'N/A';

            } else {

                $carbon_last_act = Carbon::parse($last_activity_date[0]->last_activity);
                $nurses[$i]['last_activity'] = $carbon_last_act->toDateTimeString();

                $diff = $carbon_now->diffInSeconds($carbon_last_act);

                if ($diff <= 1200 && $nurses[$i]['Time Since Last Activity'] != 'N/A') {
                    $nurses[$i]['lessThan20MinsAgo'] = true;
                }
            }

            if($nurses[$i]['Time Since Last Activity'] == 'N/A'){
                unset($nurses[$i]);
            }

            $i++;

        }

        $nurses = collect($nurses);
        $nurses->sortBy('last_activity');

        return Datatables::collection($nurses)->make(true);

    }


}
