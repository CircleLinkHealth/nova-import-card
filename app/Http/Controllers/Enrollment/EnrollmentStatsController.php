<?php

namespace App\Http\Controllers\Enrollment;

use App\CareAmbassador;
use App\CareAmbassadorLog;
use App\Enrollee;
use App\Practice;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
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

        foreach ($careAmbassadors as $ambassadorUser) {

            $ambassador = User::find($ambassadorUser)->careAmbassador;

            $base = CareAmbassadorLog::where('enroller_id', $ambassador->id)
                ->where('day', '>=', $start)
                ->where('day', '<=', $end);

            $hourCost = $ambassador->hourly_rate ?? 'Not Set';

            $data[$ambassador->id]['hourly_rate'] = $hourCost;

            $data[$ambassador->id]['name'] = User::find($ambassadorUser)->fullName;

            $data[$ambassador->id]['total_hours'] = secondsToHHMM($base->sum('total_time_in_system'));

            $data[$ambassador->id]['no_enrolled'] = $base->sum('no_enrolled');
            $data[$ambassador->id]['mins_per_enrollment'] =
                ($base->sum('no_enrolled') != 0)
                    ?
                    round(($base->sum('total_time_in_system') / 60) / $base->sum('no_enrolled'), 2)
                    : 0;

            $data[$ambassador->id]['total_calls'] = $base->sum('total_calls');


            if ($base->sum('total_calls') != 0 && $base->sum('no_enrolled') != 0 && $hourCost != 'Not Set') {

                $data[$ambassador->id]['calls_per_hour'] = round($base->sum('total_calls') / round($base->sum('total_time_in_system') / 3600,
                        1), 2);

                $data[$ambassador->id]['conversion'] = round(($base->sum('no_enrolled') / $base->sum('total_calls')) * 100,
                        2) . '%';

                $data[$ambassador->id]['per_cost'] = '$' . round((($base->sum('total_time_in_system') / 3600) * $hourCost) / $base->sum('no_enrolled'),
                        2);

            } else {

                $data[$ambassador->id]['conversion'] = 'N/A';
                $data[$ambassador->id]['calls_per_hour'] = 'N/A';
                $data[$ambassador->id]['per_cost'] = 'N/A';

            }

        }

        return Datatables::collection(collect($data))->make(true);

    }

    public function makeAmbassadorStats()
    {

        return view('admin.reports.enrollment.ambassador-kpis');

    }

    public function practiceStats(Request $request)
    {

        /*
         * Customer X
         * Unique Patients Called X
         * Yes on Call X
         * No on Call X
         * Unable to Contact X
         * Call labor Hours
         * Conversion Rate
         * Call Labor Rate
         * Calls total cost
         * Phone Acq. Cost/Pt
         */

        $input = $request->input();

        if (isset($input['start_date']) && isset($input['end_date'])) {

            $start = Carbon::parse($input['start_date'])->toDateTimeString();
            $end = Carbon::parse($input['end_date'])->toDateTimeString();

        } else {

            $start = Carbon::now()->subWeek()->toDateTimeString();
            $end = Carbon::now()->toDateTimeString();

        }

        $practices = DB::table('enrollees')->distinct('practice_id')->pluck('practice_id');

        $data = [];

        foreach ($practices as $practiceId) {

            $practice = Practice::find($practiceId);

            $data[$practice->id]['name'] = $practice->display_name;

            $base = Enrollee
                ::where('practice_id', $practice->id)
                ->where('last_attempt_at', '>=', $start)
                ->where('last_attempt_at', '<=', $end);

            $data[$practice->id]['unique_patients_called'] =
                $base->where(function ($q) {
                    $q->where('status', 'utc')
                        ->orWhere('status', 'consented')
                        ->orWhere('status', 'rejected');
                })
                    ->count();

            $data[$practice->id]['consented'] = $base->where('status', 'consented')->count();
            $data[$practice->id]['utc'] = $base->where('status', 'utc')->count();
            $data[$practice->id]['rejected'] = $base->where('status', 'rejected')->count();

            $data[$practice->id]['labor_hours'] = '';
//                CareAmbassadorLog::whereHas()

            $data[$practice->id]['hourly_rate'] = $hourCost;

            $data[$practice->id]['total_hours'] = secondsToHHMM($base->sum('total_time_in_system'));

            $data[$practice->id]['no_enrolled'] = $base->sum('no_enrolled');
            $data[$practice->id]['mins_per_enrollment'] =
                ($base->sum('no_enrolled') != 0)
                    ?
                    round(($base->sum('total_time_in_system') / 60) / $base->sum('no_enrolled'), 2)
                    : 0;

            $data[$practice->id]['total_calls'] = $base->sum('total_calls');


            if ($base->sum('total_calls') != 0 && $base->sum('no_enrolled') != 0 && $hourCost != 'Not Set') {

                $data[$practice->id]['calls_per_hour'] = round($base->sum('total_calls') / round($base->sum('total_time_in_system') / 3600,
                        1), 2);

                $data[$practice->id]['conversion'] = round(($base->sum('no_enrolled') / $base->sum('total_calls')) * 100,
                        2) . '%';

                $data[$practice->id]['per_cost'] = '$' . round((($base->sum('total_time_in_system') / 3600) * $hourCost) / $base->sum('no_enrolled'),
                        2);

            } else {

                $data[$practice->id]['conversion'] = '0%';
                $data[$practice->id]['calls_per_hour'] = 'N/A';
                $data[$practice->id]['per_cost'] = 'N/A';

            }

        }

        dd($data);

        return Datatables::collection(collect($data))->make(true);

    }

    public function makePracticeStats()
    {

        return view('admin.reports.enrollment.ambassador-kpis');

    }

}
