<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\CareCenter;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Holiday;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\NurseContactWindow;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Entities\WorkHours;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;

class WorkScheduleController extends Controller
{
    protected $holiday;
    protected $nextWeekEnd;
    protected $nextWeekStart;
    protected $nurseContactWindows;
    protected $today;
    protected $workHours;

    public function __construct(
        NurseContactWindow $nurseContactWindow,
        Holiday $holiday,
        WorkHours $workHours
    ) {
        $this->nextWeekStart = Carbon::parse('this sunday')->copy();
        $this->nextWeekEnd   = Carbon::parse('next sunday')
            ->endOfDay()
            ->addWeek(1)
            ->copy();
        $this->nurseContactWindows = $nurseContactWindow;
        $this->workHours           = $workHours;
        $this->holiday             = $holiday;
        $this->today               = Carbon::today()->copy();
    }

    public function destroy($windowId)
    {
        $window = $this->nurseContactWindows
            ->find($windowId);

        if ( ! $window) {
            $errors['window'] = 'This window does not exist.';

            return redirect()->route('care.center.work.schedule.index')
                ->withErrors($errors)
                ->withInput();
        }

        if ( ! auth()->user()->isAdmin()) {
            if ($window->nurse_info_id != auth()->user()->nurseInfo->id) {
                $errors['window'] = 'This window does not belong to you.';

                return redirect()->route('care.center.work.schedule.index')
                    ->withErrors($errors)
                    ->withInput();
            }
        }

        $window->forceDelete();

        return redirect()->back();
    }

    public function destroyHoliday($holidayId)
    {
        $holiday = $this->holiday
            ->find($holidayId);

        if ( ! $holiday) {
            $errors['holiday'] = 'This holiday does not exist.';

            return redirect()->route('care.center.work.schedule.index')
                ->withErrors($errors)
                ->withInput();
        }

        if (auth()->user()->nurseInfo) {
            if ($holiday->nurse_info_id != auth()->user()->nurseInfo->id) {
                $errors['holiday'] = 'This holiday does not belong to you.';

                return redirect()->route('care.center.work.schedule.index')
                    ->withErrors($errors)
                    ->withInput();
            }
        }

        $holiday->forceDelete();

        return redirect()->back();
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getAllNurseSchedules()
    {
        $nurses = User::// ofType('care-center')
        with('nurseInfo.windows')
            ->whereHas('nurseInfo', function ($q) {
                $q->where('status', 'active');
            })
            ->whereHas('nurseInfo.windows')
            ->get()
            ->sortBy('first_name');

        $calendarData = $nurses->map(function ($nurse) {
            return collect($nurse->nurseInfo->windows)->map(function ($window) use ($nurse) {
                $weekMap = [
                    1 => Carbon::parse($window->date)->startOfWeek()->toDateString(),
                    2 => Carbon::parse($window->date)->startOfWeek()->addDay(1)->toDateString(),
                    3 => Carbon::parse($window->date)->startOfWeek()->addDay(2)->toDateString(),
                    4 => Carbon::parse($window->date)->startOfWeek()->addDay(3)->toDateString(),
                    5 => Carbon::parse($window->date)->startOfWeek()->addDay(4)->toDateString(),
                    6 => Carbon::parse($window->date)->startOfWeek()->addDay(5)->toDateString(),
                    7 => Carbon::parse($window->date)->startOfWeek()->addDay(6)->toDateString(),
                ];

                $dayInHumanLang = Carbon::parse($weekMap[$window->day_of_week])->format('l');

                $workHoursForDay = WorkHours::where('workhourable_id', $nurse->nurseInfo->id)->pluck($dayInHumanLang)->first();

                return collect([
                    'title' => "$nurse->display_name: $workHoursForDay Hrs",
                    'start' => "{$weekMap[$window->day_of_week]}T{$window->window_time_start}",
                    'end'   => "{$weekMap[$window->day_of_week]}T{$window->window_time_end}",
                ]);
            });
        })->flatten(1);

        $tzAbbr = auth()->user()->timezone_abbr ?? 'EDT';
        
        return view('admin.nurse.schedules.index', compact(['data', 'tzAbbr']));
    }

    public function index()
    {
        $nurse = auth()->user()->nurseInfo;

        $windows = $this->nurseContactWindows
            ->whereNurseInfoId($nurse->id)
            ->get()
            ->sortBy(function ($item) {
                return Carbon::createFromFormat(
                    'H:i:s',
                    "{$item->window_time_start}"
                );
            });

        $holidays         = $nurse->upcoming_holiday_dates;
        $holidaysThisWeek = $nurse->holidays_this_week;

        $tzAbbr = auth()->user()->timezone_abbr;

        //I think time tracking submits along with the form, thus messing up sessions.
        //Temporary fix
        $disableTimeTracking = true;

        return view('care-center.work-schedule', compact([
            'disableTimeTracking',
            'holidays',
            'holidaysThisWeek',
            'windows',
            'tzAbbr',
            'nurse',
        ]));
    }

    public function store(Request $request)
    {
        $isAdmin = auth()->user()->isAdmin();

        $nurseInfoId = $isAdmin
            ? $request->input('nurse_info_id')
            : auth()->user()->nurseInfo->id;

        if ( ! $nurseInfoId) {
            $nurseInfoId = auth()->user()->nurseInfo->id;
        }

        $validator = Validator::make($request->all(), [
            'day_of_week'       => 'required',
            'window_time_start' => 'required|date_format:H:i',
            'window_time_end'   => 'required|date_format:H:i|after:window_time_start',
        ]);

        $windowExists = NurseContactWindow::where([
            [
                'nurse_info_id',
                '=',
                $nurseInfoId,
            ],
            [
                'window_time_end',
                '>=',
                $request->input('window_time_start'),
            ],
            [
                'window_time_start',
                '<=',
                $request->input('window_time_end'),
            ],
            [
                'day_of_week',
                '=',
                $request->input('day_of_week'),
            ],
        ])->first();

        $hoursSum = NurseContactWindow::where([
            ['nurse_info_id', '=', $nurseInfoId],
            ['day_of_week', '=', $request->input('day_of_week')],
        ])
            ->get()
            ->sum(function ($window) {
                return Carbon::createFromFormat(
                    'H:i:s',
                    $window->window_time_end
                )->diffInHours(Carbon::createFromFormat(
                        'H:i:s',
                        $window->window_time_start
                    ));
                }) + Carbon::createFromFormat(
                    'H:i',
                    $request->input('window_time_end')
                )->diffInHours(Carbon::createFromFormat(
                'H:i',
                $request->input('window_time_start')
            ));

        $invalidWorkHoursNumber = false;

        if ($hoursSum < $request->input('work_hours')) {
            $invalidWorkHoursNumber = true;
        }

        if ($validator->fails() || $windowExists || $invalidWorkHoursNumber) {
            if ($windowExists) {
                $validator->getMessageBag()->add(
                    'window_time_start',
                    'This window is overlapping with an already existing window.'
                );
            }

            if ($invalidWorkHoursNumber) {
                $validator->getMessageBag()->add(
                    'work_hours',
                    'Daily work hours cannot be more than total window hours.'
                );
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with(['editedNurseId' => $nurseInfoId]);
        }

        if ($isAdmin) {
            $user = auth()->user();

            $window = $this->nurseContactWindows->create([
                'nurse_info_id'     => $nurseInfoId,
                'date'              => Carbon::now()->format('Y-m-d'),
                'day_of_week'       => $request->input('day_of_week'),
                'window_time_start' => $request->input('window_time_start'),
                'window_time_end'   => $request->input('window_time_end'),
            ]);

            $nurseUser = Nurse::find($nurseInfoId)->user;

            $dayName      = clhDayOfWeekToDayName($window->day_of_week);
            $nurseMessage = "Admin {$user->display_name} assigned Nurse {$nurseUser->display_name} to work for";
            $message      = "${nurseMessage} {$request->input('work_hours')} hours on ${dayName} between {$window->range()->start->format('h:i A T')} to {$window->range()->end->format('h:i A T')}";
            sendSlackMessage('#carecoachscheduling', $message);
        } else {
            $user = auth()->user();

            $window = $user->nurseInfo->windows()->create([
                'date'              => Carbon::now()->format('Y-m-d'),
                'day_of_week'       => $request->input('day_of_week'),
                'window_time_start' => $request->input('window_time_start'),
                'window_time_end'   => $request->input('window_time_end'),
            ]);
        }

        $workHours = $this->workHours->updateOrCreate([
            'workhourable_type' => Nurse::class,
            'workhourable_id'   => $nurseInfoId,
        ], [
            strtolower(clhDayOfWeekToDayName($request->input('day_of_week'))) => $request->input('work_hours'),
        ]);

        return redirect()->back()->with(['editedNurseId' => $nurseInfoId]);
    }

    public function storeHoliday(Request $request)
    {
        $user = auth()->user();

        $request->replace([
            'holiday' => Carbon::parse($request->input('holiday'))->toDateTimeString(),
        ]);

        $validator = Validator::make($request->all(), [
            'holiday' => [
                Rule::unique('holidays', 'date')->where(function ($query) use ($user) {
                    $query->where('nurse_info_id', $user->nurseInfo->id);
                }),
                'required',
                'date',
                'after:tomorrow',
            ],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $holiday = $user->nurseInfo->holidays()->create([
            'date' => Carbon::parse($request->input('holiday'))->format('Y-m-d'),
        ]);

        return redirect()->back();
    }

    public function updateDailyHours(Request $request, $id)
    {
        $workHours                           = WorkHours::find($id);
        $workHours->{$request->input('day')} = $request->input('workHours');
        $workHours->save();

        return response()->json();
    }

    protected function canAddNewWindow(Carbon $date)
    {
        return ($date->gt($this->nextWeekStart) && $this->today->dayOfWeek < 4)
            || $date->gt($this->nextWeekEnd);
    }
}
