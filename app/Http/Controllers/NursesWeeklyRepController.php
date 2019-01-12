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
                          'outboundCalls'        => function ($q) use ($dayCounter, $last) {
                              $q->whereBetween('scheduled_date', [$dayCounter, $last])
                                ->orWhereBetween('called_date', [$dayCounter, $last]);
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
                'scheduledCalls'    => $nurse->outboundCalls->where('status', 'scheduled')->count(),
                'actualCalls'       => $nurse->outboundCalls->whereIn('status', ['reached', 'not reached'])->count(),
                'successful'        => $nurse->outboundCalls->where('status', 'reached')->count(),
                'unsuccessful'      => $nurse->outboundCalls->whereIn('status', ['not reached', 'dropped'])->count(),
            ];
        }
//dd($x);

        /*       $x = collect();
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

             */
        $nurses = $x;

        return view('admin.reports.nurseweekly', compact('nurses', 'dayCounter'));
    }
}
