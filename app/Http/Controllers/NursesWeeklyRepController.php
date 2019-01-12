<?php

namespace App\Http\Controllers;

use App\User;
use Carbon\Carbon;

class NursesWeeklyRepController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dayCounter = Carbon::now()->startOfWeek()->startOfDay();
        $last       = Carbon::now()->endOfWeek()->endOfDay();

        $nurses = User::ofType('care-center')
                      ->with([
                          'nurseInfo.windows',
                          'pageTimersAsProvider' => function ($q) use ($dayCounter, $last) {
                              $q->whereBetween('start_time', [$dayCounter, $last]);
                          },
                      ])
                      ->whereHas('outboundCalls', function ($q) use ($dayCounter, $last) {
                          $q->whereBetween('scheduled_date', [$dayCounter, $last]);
                      })->get();

        $x = collect();
        foreach ($nurses as $nurse) {
            $x[] = [
                'actualWorkhours'   => $nurse->pageTimersAsProvider,
                'name'              => $nurse->first_name,
                'commitedWorkhours' => $nurse->nurseInfo->windows,
                'scheduledCalls'    => $nurse->countScheduledCallsFor($dayCounter),
                'completedCalls'    => $nurse->countCompletedCallsFor($dayCounter),
                'successful'        => $nurse->countSuccessfulCallsFor($dayCounter),
                'unsuccessful'      => $nurse->countUnSuccessfulCallsFor($dayCounter),
            ];
        }

        return view('admin.reports.nurseweekly', compact('x', 'dayCounter'));

    }

//                          'outboundCalls' => function ($nurses) use (&$rows, $dayCounter, $last) {
//                              $nurses->whereBetween('scheduled_date', [$dayCounter, $last]);
//                          },


    /* $weekStart = Carbon::now()->startOfWeek()->toDateString();
    $weekEnd   = Carbon::now()->endOfWeek()->toDateString();
    $calls     = [];
    $nurses    = User::ofType('care-center')->whereHas('outboundCalls', function ($q) use ($weekStart, $weekEnd) {
        $q->whereBetween('scheduled_date', [$weekStart, $weekEnd]);

    })->with([
        'outboundCalls' => function ($q) use ($weekStart, $weekEnd) {
            $q->whereBetween('scheduled_date', [$weekStart, $weekEnd]);

        },
    ])->chunk(50, function ($nurses) use (&$calls, $weekStart, $weekEnd) {
        foreach ($nurses as $nurse) {
             $calls[$nurse->display_name] = $nurse->outboundCalls
                 ->where('scheduled_date', '>=', $weekStart)
                 ->where('scheduled_date', '<=', $weekEnd)
                 ->transform(function ($call) {
                     return collect(array_merge($call->toArray(), [
                         'status'        => $call->status,

                     ]));
                 });*/
    /* ->mapToGroups(function ($call) {
       return [$call->status => $call->outbound_cpm_id];
     });*/
    /*       }*/

    /*   });*/


    /*  return view('admin.reports.nurseweekly', compact('nurses'));*/
//}
}
