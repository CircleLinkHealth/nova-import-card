<?php

namespace App\Http\Controllers;

use App\Http\Requests\Request;
use App\User;
use Carbon\Carbon;

class NursesWeeklyRepController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $date = Carbon::now()->startOfWeek()->startOfDay();
        $nurses    = User::ofType('care-center')
                         ->with([
                             'nurseInfo.windows',
                             'pageTimersAsProvider' => function ($q) use ($date) {
                                 $q->where('start_time', $date);
                             },
                             'outboundCalls'        => function ($q) use ($date) {
                                 $q->where('scheduled_date', $date)
                                   ->orWhere('called_date', $date);
                             },
                         ])->whereHas('outboundCalls', function ($q) use ($date) {
                $q->where('scheduled_date', $date)
                  ->orWhere('called_date', $date);
            })->get();

        $x = [];
        foreach ($nurses as $nurse) {
            $x[] = [
                'actualWorkhours'   => $nurse->pageTimersAsProvider,
                'name'              => $nurse->first_name,
                'commitedWorkhours' => $nurse->nurseInfo->windows,
                'scheduledCalls'    => $nurse->outboundCalls->where('status', 'scheduled')->count(),
                'actualCalls'       => $nurse->outboundCalls->whereIn('status', ['reached', 'not reached', 'dropped'])->count(),
                'successful'        => $nurse->outboundCalls->where('status', 'reached')->count(),
                'unsuccessful'      => $nurse->outboundCalls->whereIn('status', ['not reached', 'dropped'])->count(),
                /*'user_id'           => $nurse->nurseInfo->user_id,
                'nurse_info_id'     => $nurse->nurseInfo->id,*/
            ];
        }
        $nurses = $x;

        return view('admin.reports.nurseweekly', compact('nurses'));
    }
}
