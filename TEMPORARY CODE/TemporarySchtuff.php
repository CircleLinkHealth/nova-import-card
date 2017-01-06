<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 25/10/16
 * Time: 11:37 AM
 */

//Create activities from PageTimers that were logged from double tabs
//
//

use App\Activity;
use App\PageTimer;
use App\Patient;
use Carbon\Carbon;
use Carbon\Carbon;

$all = App\PageTimer::onlyTrashed()->get();
dd($all->count());
foreach ($all as $pt) {

    $start = Carbon::parse($pt->start_time);
    $end = Carbon::parse($pt->end_time);

    $pt->billable_duration = $start->diffInSeconds($end);
    $pt->redirect_to = '';
    $pt->processed = 'Y';
    $pt->save();
    $pt->restore();

    $activiyParams['type'] = $pt->activity_type;
    $activiyParams['provider_id'] = $pt->provider_id;
    $activiyParams['performed_at'] = $pt->start_time;
    $activiyParams['duration'] = $pt->billable_duration;
    $activiyParams['duration_unit'] = 'seconds';
    $activiyParams['patient_id'] = $pt->patient_id;
    $activiyParams['logged_from'] = 'pagetimer';
    $activiyParams['logger_id'] = $pt->provider_id;
    $activiyParams['page_timer_id'] = $pt->id;

    // if rule exists, create activity
    $activityId = App\Activity::createNewActivity($activiyParams);

    $activityService = new App\Services\ActivityService;
    $result = $activityService->reprocessMonthlyActivityTime($pt->patient_id);
}







//Testing CCM Time overlaps
//
//
//
//

//use Carbon\Carbon;
//
//$x = Carbon::parse('2016-10-23 15:19:05');
//$y = Carbon::parse('0000-00-00 00:00:00');
//
//dd($x->diffInSeconds($y));
//
//$startTime = Carbon\Carbon::now()->copy()->addSeconds(10);
//$endTime = $startTime->copy()->addSeconds(60);
//
//$create = new Illuminate\Support\Collection([
//    [
//        '2016-10-23 15:18:57',
//        '2016-10-23 15:19:03',
//        6,
//        'Patient Overview Review',
//    ],
//    [
//        '2016-10-23 15:18:59',
//        '2016-10-23 15:19:04',
//        5,
//        'Patient Overview Review',
//    ],[
//        '2016-10-23 15:19:02',
//        '2016-10-23 15:19:06',
//        4,
//        'Patient Overview Review',
//    ],[
//        '2016-10-23 15:19:02',
//        '2016-10-23 15:19:17',
//        15,
//        'Patient Overview Review',
//    ],
//    [
//        '2016-10-23 15:18:57',
//        '2016-10-23 15:19:01',
//        4,
//        'Patient Overview Review',
//    ],
//    [
//        '2016-10-23 15:19:02',
//        '2016-10-23 15:19:06',
//        4,
//        'Patient Overview Review',
//    ],
//    [
//        '2016-10-23 15:19:16',
//        '2016-10-23 15:19:20',
//        4,
//        'Patient Overview Review',
//    ],
//    [
//        '2016-10-23 15:16:36',
//        '2016-10-23 15:19:31',
//        175,
//        'non ccm',
//    ],
//]);
//
//foreach ($create as $c) {
//    $request = new Illuminate\Http\Request();
//
//    $request->merge([
//        'patientId'        => 285,
//        'providerId'       => 357,
//        'totalTime'        => $c[2] * 1000,
//        'programId'        => 9,
//        'startTime'        => $c[0],
//        'testEndTime'      => $c[1],
//        'urlFull'          => 'www.url.com',
//        'urlShort'         => 'url.com',
//        'ipAddr'           => '1.1.1.1',
//        'activity'         => $c[3],
//        'title'            => $c[3],
//        'testing'          => true,
//        'redirectLocation' => '',
//    ]);
//
//    (new App\Http\Controllers\PageTimerController($request, new App\Services\TimeTracking\Service))->store($request);
//}


/**
 * Used to reset CCM Time, that time it went south. #december #2016 #hack
 */

if (isset($_GET['reset'])) {
    if ($_GET['reset'] == 'kra') {
        Patient::withTrashed()
            ->update([
                'cur_month_activity_time' => '0',
            ]);

        $acts = Activity::where('performed_at', '>=', Carbon::now()->startOfMonth())
            ->where('performed_at', '<=', Carbon::now()->endOfMonth())
            ->groupBy('patient_id')
            ->selectRaw('sum(duration) as total_duration, patient_id')
            ->pluck('total_duration', 'patient_id');

        foreach ($acts as $id => $ccmTime) {
            $info = Patient::whereUserId($id)
                ->first();

            if ($info) {
                $info->cur_month_activity_time = $ccmTime;
                $info->save();
            }
        }

        dd($acts);
    }
}