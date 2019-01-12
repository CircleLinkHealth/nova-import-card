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
        $startDate = Carbon::now()->startOfWeek()->startOfDay();
        $lastDate  = Carbon::now()->endOfWeek()->endOfDay();
        $nurses    = User::ofType('care-center')
                         ->with([
                             'nurseInfo.windows',
                             'pageTimersAsProvider' => function ($q) use ($startDate, $lastDate) {
                                 $q->whereBetween('start_time', [$startDate, $lastDate]);
                             },
                             'outboundCalls'        => function ($q) use ($startDate, $lastDate) {
                                 $q->whereBetween('scheduled_date', [$startDate, $lastDate])
                                   ->orWhereBetween('called_date', [$startDate, $lastDate]);
                             },
                         ])
                         ->whereHas('outboundCalls', function ($q) use ($startDate, $lastDate) {
                             $q->whereBetween('scheduled_date', [$startDate, $lastDate])
                               ->orWhereBetween('called_date', [$startDate, $lastDate]);
                         })->get();

        $x = collect();
        foreach ($nurses as $nurse) {
            $x[] = [
                'actualWorkhours'   => $nurse->pageTimersAsProvider,
                'name'              => $nurse->first_name,
                'commitedWorkhours' => $nurse->nurseInfo->windows,
                'scheduledCalls'    => $nurse->outboundCalls->where('status', 'scheduled')->count(),
                'actualCalls'       => $nurse->outboundCalls->whereIn('status', ['reached', 'not reached', 'dropped'])->count(),
                'successful'        => $nurse->outboundCalls->where('status', 'reached')->count(),
                'unsuccessful'      => $nurse->outboundCalls->whereIn('status', ['not reached', 'dropped'])->count(),
            ];
        }


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
