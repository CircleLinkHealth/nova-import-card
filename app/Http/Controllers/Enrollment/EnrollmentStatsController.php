<?php

namespace App\Http\Controllers\Enrollment;

use App\CareAmbassador;
use App\CareAmbassadorLog;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Facades\Datatables;
use Illuminate\Support\Facades\Log;


class EnrollmentStatsController extends Controller
{

    public function ambassadorStats(Request $request)
    {

        $input = $request->input();

        if (isset($input['start_date']) && isset($input['end_date'])) {

            $start = Carbon::parse($input['start_date'])->toDateString();
            $end = Carbon::parse($input['end_date'])->toDateString();

        } else {

            $start = Carbon::now()->subWeek()->toDateString();
            $end = Carbon::now()->toDateString();

        }

        $careAmbassadors = User::whereHas('roles', function ($q) {

            $q->where('name', 'care-ambassador');

        })->pluck('id');

        $data = [];

        foreach ($careAmbassadors as $ambassador) {

            $base = CareAmbassadorLog::where('care_ambassador_id', $ambassador)
                ->where('day', '>=', $start)
                ->where('day', '<=', $end);

            //@todo implement
            $hourCost = CareAmbassador::where('user_id', $ambassador)->first()['hourly_rate'] ?? 'Not Set';

            $data[$ambassador]['hourly_rate'] = $hourCost;

            $data[$ambassador]['name'] = User::find($ambassador)->fullName;

            $data[$ambassador]['total_hours'] = secondsToHHMM($base->sum('total_time_in_system'));

            $data[$ambassador]['no_enrolled'] = $base->sum('no_enrolled');
            $data[$ambassador]['mins_per_enrollment'] =
                ($base->sum('no_enrolled') != 0)
                    ?
                    ($base->sum('total_time_in_system') / 60) / $base->sum('no_enrolled')
                    : 0;

            $data[$ambassador]['total_calls'] = $base->sum('total_calls');

            if ($base->sum('total_calls') != 0 && $base->sum('no_enrolled') != 0 && $hourCost != 'Not Set') {

                $data[$ambassador]['conversion'] = round(($base->sum('no_enrolled') / $base->sum('total_calls')) * 100, 2) . '%';

                $data[$ambassador]['per_cost'] = '$'. round((($base->sum('total_time_in_system') / 3600) * $hourCost) / $base->sum('no_enrolled'), 2);

            } else {

                $data[$ambassador]['conversion'] = '0%';

                $data[$ambassador]['per_cost'] = 'N/A';

            }

        }

        return Datatables::collection(collect($data))->make(true);

    }

    public function makeAmbassadorStats()
    {

        return view('admin.reports.enrollment.ambassador-kpis');

    }

}
