<?php namespace App\Services;

use App\Activity;
use App\PatientInfo;
use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Collection;

class ActivityService
{
    /**
     * Get total activity for a range of two Carbon dates.
     *
     * @param $userId
     * @param Carbon $from
     * @param Carbon $to
     * @return mixed
     */

    public function getOfflineActivitiesForPatient(User $patient){

        return Activity::select(DB::raw('*'))
            ->where('patient_id', $patient->ID)
            ->where('logged_from', 'manual_input')
            ->groupBy(DB::raw('provider_id, DATE(performed_at),type'))
            ->orderBy('performed_at', 'desc')
            ->get();

    }

    public function getTotalActivityTimeForRange($userId, Carbon $from, Carbon $to)
    {
        $acts = new Collection(DB::table('lv_activities')
            ->select(DB::raw('id,provider_id,logged_from,DATE(performed_at), type, SUM(duration) as duration'))
            ->whereBetween('performed_at', [
                $from, $to
            ])
            ->where('patient_id', $userId)
            ->where(function ($q) {
                $q->where('logged_from', 'activity')
                    ->Orwhere('logged_from', 'manual_input')
                    ->Orwhere('logged_from', 'pagetimer');
            })
            ->groupBy(DB::raw('provider_id, DATE(performed_at),type'))
            ->orderBy('performed_at', 'desc')
            ->get()
        );

        return $acts->map(function ($act) {
            return $act->duration;
        })->sum();
    }

    public function getTotalActivityTimeForMonth($userId, $month = false, $year = false)
    {
        // if no month, set to current month
        if (!$month) {
            $month = date('m');
        }
        if (!$year) {
            $year = date('Y');
        }

        $time = Carbon::createFromDate($year, $month, 15);
        $start = $time->startOfMonth()->format('Y-m-d') . ' 00:00:00';
        $end = $time->endOfMonth()->format('Y-m-d') . ' 23:59:59';
        $month_selected = $time->format('m');
        $month_selected_text = $time->format('F');
        $year_selected = $time->format('Y');

        $acts = DB::table('lv_activities')
            ->select(DB::raw('id,provider_id,logged_from, performed_at, type, SUM(duration) as duration'))
            ->whereBetween('performed_at', [
                $start, $end
            ])
            ->where('patient_id', $userId)
            ->where(function ($q) {
                $q->where('logged_from', 'activity')
                    ->Orwhere('logged_from', 'manual_input')
                    ->Orwhere('logged_from', 'pagetimer');
            })
            ->groupBy(DB::raw('provider_id, performed_at,type'))
            ->orderBy('performed_at', 'desc')
            ->get();

        $totalDuration = 0;
        foreach ($acts as $act) {
            $totalDuration = ($totalDuration + $act->duration);
        }

        /*
        $totalDuration = Activity::where( \DB::raw('MONTH(performed_at)'), '=', $month )->where( \DB::raw('YEAR(performed_at)'), '=', $year )->where( 'patient_id', '=', $userId )->sum('duration');
        */
        return $totalDuration;
    }

    public function reprocessMonthlyActivityTime($userIds = false, $month = false, $year = false)
    {
        // if no month, set to current month
        if (!$month) {
            $month = date('m');
        }
        if (!$year) {
            $year = date('Y');
        }

        if ($userIds) {
            // cast userIds to array if string
            if (!is_array($userIds)) {
                $userIds = array($userIds);
            }
            $users = User::whereIn('id', $userIds)->orderBy('ID', 'desc')->get();
        } else {
            // get all users
            $users = User::orderBy('ID', 'desc')->get();
        }

        if (!empty($users)) {
            // loop through each user
            foreach ($users as $user) {
                // get all activities for user for month
                $totalDuration = $this->getTotalActivityTimeForMonth($user->ID, $month, $year);

                // update cur_month_activity_time with total
                PatientInfo::updateOrCreate([
                    'user_id' => $user->ID
                ], [
                    'cur_month_activity_time' => $totalDuration
                ]);
            }
        }
        return true;
    }

}
