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
        $data   = [];
        $date   = Carbon::now()->startOfWeek()->startOfDay();
        $nurses = User::ofType('care-center')
                      ->with([
                          'nurseInfo.windows',
                          'pageTimersAsProvider' => function ($q) use ($date) {
                              $q->where([
                                  ['start_time', '>=', $date->copy()->startOfDay()->toDateTimeString()],
                                  ['end_time', '<=', $date->copy()->endOfDay()->toDateTimeString()],
                              ]);
                          },
                          'outboundCalls'        => function ($q) use ($date) {
                              $q->where('scheduled_date', $date->toDateString())
                                ->orWhere('called_date', '>=', $date->toDateTimeString())
                                ->where('called_date', '<=', $date->copy()->endOfDay()->toDateTimeString());
                          },
                      ])->whereHas('outboundCalls', function ($q) use ($date) {
                $q->where('scheduled_date', $date->toDateString())
                  ->orWhere('called_date', '>=', $date->toDateTimeString());
            })->chunk(10, function ($nurses) use (&$data, $date) {
                foreach ($nurses as $nurse) {
                    $data[] = [
                        'nurse_info_id'  => $nurse->nurseInfo->id,
                        'name'           => $nurse->first_name,
                        'last_name'      => $nurse->last_name,
                        'actualHours'    => $nurse->pageTimersAsProvider->sum('billable_duration'),
                        'committedHours' => $nurse->nurseInfo->windows->where('day_of_week',
                            carbonToClhDayOfWeek($date->dayOfWeek))->sum(function ($window) {
                            return $window->numberOfHoursCommitted();
                        }),
                        'scheduledCalls' => $nurse->outboundCalls->where('status', 'scheduled')->count(),
                        'actualCalls'    => $nurse->outboundCalls->whereIn('status',
                            ['reached', 'not reached', 'dropped'])->count(),
                        'successful'     => $nurse->outboundCalls->where('status', 'reached')->count(),
                        'unsuccessful'   => $nurse->outboundCalls->whereIn('status',
                            ['not reached', 'dropped'])->count(),
                    ];
                }
            });
dd($data);
        return view('admin.reports.nurseweekly', compact('data'));
    }
}
