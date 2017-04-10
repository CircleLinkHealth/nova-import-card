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

                $data[$ambassador->id]['earnings'] = '$' . round($hourCost * ( $base->sum('total_time_in_system') / 3600 ), 2);

                $data[$ambassador->id]['calls_per_hour'] = round($base->sum('total_calls') / $base->sum('total_time_in_system') / 3600, 2);

                $data[$ambassador->id]['conversion'] = round(($base->sum('no_enrolled') / $base->sum('total_calls')) * 100,
                        2) . '%';

                $data[$ambassador->id]['per_cost'] = '$' . round((($base->sum('total_time_in_system') / 3600) * $hourCost) / $base->sum('no_enrolled'),
                        2);

            } else {

                $data[$ambassador->id]['earnings'] = 'N/A';
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

        $input = $request->input();

        if (isset($input['start_date']) && isset($input['end_date'])) {

            $start = Carbon::parse($input['start_date'])->toDateTimeString();
            $end = Carbon::parse($input['end_date'])->endOfDay()->toDateTimeString();

        } else {

            $start = Carbon::now()->subWeek()->toDateTimeString();
            $end = Carbon::now()->toDateTimeString();

        }

        $practices = DB::table('enrollees')->distinct('practice_id')->pluck('practice_id');

        $data = [];

        foreach ($practices as $practiceId) {

            $practice = Practice::find($practiceId);

            $data[$practice->id]['name'] = $practice->display_name;

            $data[$practice->id]['unique_patients_called'] =
                Enrollee
                    ::where('practice_id', $practice->id)
                    ->where('last_attempt_at', '>=', $start)
                    ->where('last_attempt_at', '<=', $end)->where(function ($q) {
                        $q->where('status', 'utc')
                            ->orWhere('status', 'consented')
                            ->orWhere('status', 'rejected');
                    })
                    ->count();

            $data[$practice->id]['consented'] = Enrollee
                ::where('practice_id', $practice->id)
                ->where('last_attempt_at', '>=', $start)
                ->where('last_attempt_at', '<=', $end)->where('status', 'consented')->count();

            $data[$practice->id]['utc'] = Enrollee
                ::where('practice_id', $practice->id)
                ->where('last_attempt_at', '>=', $start)
                ->where('last_attempt_at', '<=', $end)->where('status', 'utc')->count();

            $data[$practice->id]['rejected'] = Enrollee
                ::where('practice_id', $practice->id)
                ->where('last_attempt_at', '>=', $start)
                ->where('last_attempt_at', '<=', $end)->where('status', 'rejected')->count();

            $total_time = Enrollee
                ::where('practice_id', $practice->id)
                ->where('last_attempt_at', '>=', $start)
                ->where('last_attempt_at', '<=', $end)
                ->sum('total_time_spent');

            $data[$practice->id]['labor_hours'] =
                secondsToHHMM($total_time);

            $enrollers = Enrollee
                ::select(DB::raw('care_ambassador_id, sum(total_time_spent) as total'))
                ->where('practice_id', $practice->id)
                ->where('last_attempt_at', '>=', $start)
                ->where('last_attempt_at', '<=', $end)
                ->groupBy('care_ambassador_id')->pluck('total', 'care_ambassador_id');

            $data[$practice->id]['total_cost'] = 0;

            foreach ($enrollers as $enrollerId => $time) {

                $enroller = CareAmbassador::find($enrollerId);
                $data[$practice->id]['total_cost'] += $enroller->hourly_rate * round($time / 3600, 2);

            }

            if ($data[$practice->id]['unique_patients_called'] > 0 && $data[$practice->id]['consented'] > 0) {

                $data[$practice->id]['conversion'] =
                    round($data[$practice->id]['consented'] / $data[$practice->id]['unique_patients_called'] * 100,
                        2) . '%';

            } else {

                $data[$practice->id]['conversion'] = 'N/A';

            }

            if ($data[$practice->id]['total_cost'] > 0 && $data[$practice->id]['consented'] > 0) {

                $data[$practice->id]['acq_cost'] = $data[$practice->id]['total_cost'] / $data[$practice->id]['consented'];

            } else {

                $data[$practice->id]['acq_cost'] = 'N/A';

            }

            if ($data[$practice->id]['total_cost'] > 0 && $total_time > 0){
                $data[$practice->id]['labor_rate'] = round($data[$practice->id]['total_cost'] / ($total_time / 3600), 2);
            } else {
                $data[$practice->id]['labor_rate'] = 'N/A';

            }

        }

        return Datatables::collection(collect($data))->make(true);

    }

    public function makePracticeStats()
    {

        return view('admin.reports.enrollment.practice-kpis');

    }

}
