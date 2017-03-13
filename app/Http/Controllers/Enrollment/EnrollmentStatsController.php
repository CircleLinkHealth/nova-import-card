<?php

namespace App\Http\Controllers\Enrollment;

use App\CareAmbassadorLog;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Facades\Datatables;

class EnrollmentStatsController extends Controller
{

    public function ambassadorStats(Request $request)
    {

        $input = $request->input();

        if (isset($input['start']) && isset($input['end'])) {

            $start = Carbon::parse($input['start'])->toDateString();
            $end = Carbon::parse($input['end'])->toDateString();

        } else {

            $start = Carbon::now()->subWeek()->toDateString();
            $end = Carbon::now()->toDateString();

        }

        $careAmbassadors = \App\User::whereHas('roles', function ($q) {

            $q->where('name', 'care-ambassador');

        })->pluck('id');

        $data = [];

        foreach ($careAmbassadors as $ambassador) {

            $base = CareAmbassadorLog::where('care_ambassador_id', $ambassador)
                ->where('day', '>=', Carbon::now()->subWeek()->toDateString())
                ->where('day', '<=', Carbon::now()->toDateString())->get();

            //@todo implement
            $hourCost = 15;

            $data[$ambassador]['name'] = User::find($ambassador)->fullName;

            $data[$ambassador]['total_hours'] = secondsToMMSS($base->sum('total_time_in_system'));
            $data[$ambassador]['no_enrolled'] = $base->sum('no_enrolled');
            $data[$ambassador]['mins_per_enrollment'] =
                ($base->sum('no_enrolled') != 0)
                    ?
                    ($base->sum('total_time_in_system') / 60) / $base->sum('no_enrolled')
                    : 0;
            $data[$ambassador]['total_calls'] = $base->sum('total_calls');

            if ($base->sum('total_calls') != 0 && $base->sum('no_enrolled') != 0) {
                $data[$ambassador]['conversion'] = ($base->sum('no_enrolled') / $base->sum('total_calls')) * 100 . '%';
                $data[$ambassador]['per_cost'] = (($base->sum('total_time_in_system') / 3600) * $hourCost) / $base->sum('no_enrolled');
            } else {
                $data[$ambassador]['conversion'] = '0%';
                $data[$ambassador]['per_cost'] = 'N/A';
            }

        }

        debug($data);

        return Datatables::collection(collect($data))->make(true);

    }

    public function makeAmbassadorStats()
    {

        return view('admin.reports.enrollment.ambassador-kpis');

    }

}
