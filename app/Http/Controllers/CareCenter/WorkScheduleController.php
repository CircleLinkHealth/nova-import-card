<?php

namespace App\Http\Controllers\CareCenter;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Models\WorkHours;
use App\Nurse;
use App\NurseContactWindow;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
        $this->nextWeekEnd = Carbon::parse('next sunday')
            ->endOfDay()
            ->addWeek(1)
            ->copy();
        $this->nurseContactWindows = $nurseContactWindow;
        $this->workHours = $workHours;
        $this->holiday = $holiday;
        $this->today = Carbon::today()->copy();
    }

    public function index()
    {
        $nurse = auth()->user()->nurseInfo;

        $windows = $this->nurseContactWindows
            ->whereNurseInfoId($nurse->id)
            ->get()
            ->sortBy(function ($item) {
                return Carbon::createFromFormat('H:i:s',
                    "$item->window_time_start");
            });

        $holidays = $nurse->upcoming_holiday_dates;
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

    public function storeHoliday(Request $request)
    {
        $request->replace([
            'holiday' => Carbon::parse($request->input('holiday'))->toDateTimeString(),
        ]);

        $validator = Validator::make($request->all(), [
            'holiday' => "required|date|after:tomorrow|unique:holidays,date",
        ]);


        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = auth()->user();

        $holiday = $user->nurseInfo->holidays()->create([
            'date' => Carbon::parse($request->input('holiday'))->format('Y-m-d'),
        ]);

        $message = "Nurse {$user->display_name} just added a holiday on {$holiday->date->format('l, F j Y')}";

        sendSlackMessage('#carecoachscheduling', $message);

        return redirect()->back();
    }

    public function store(Request $request)
    {
        $isAdmin = auth()->user()->hasRole('administrator');

        $nurseInfoId = $isAdmin
            ? $request->input('nurse_info_id')
            : auth()->user()->nurseInfo->id;

        if (!$nurseInfoId) {
            $nurseInfoId = auth()->user()->nurseInfo->id;
        }

        $validator = Validator::make($request->all(), [
            'day_of_week'       => "required",
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
                    return Carbon::createFromFormat('H:i:s',
                        $window->window_time_end)->diffInHours(Carbon::createFromFormat('H:i:s',
                        $window->window_time_start));
                }) + Carbon::createFromFormat('H:i',
                $request->input('window_time_end'))->diffInHours(Carbon::createFromFormat('H:i',
                $request->input('window_time_start')));

        $invalidWorkHoursNumber = false;

        if ($hoursSum < $request->input('work_hours')) {
            $invalidWorkHoursNumber = true;
        }

        if ($validator->fails() || $windowExists || $invalidWorkHoursNumber) {
            if ($windowExists) {
                $validator->getMessageBag()->add('window_time_start',
                    'This window is overlapping with an already existing window.');
            }

            if ($invalidWorkHoursNumber) {
                $validator->getMessageBag()->add('work_hours',
                    'Daily work hours cannot be more than total window hours.');
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
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

            $nurseMessage = "Admin {$user->display_name} assigned Nurse {$nurseUser->display_name} to work for";
        } else {
            $user = auth()->user();

            $window = $user->nurseInfo->windows()->create([
                'date'              => Carbon::now()->format('Y-m-d'),
                'day_of_week'       => $request->input('day_of_week'),
                'window_time_start' => $request->input('window_time_start'),
                'window_time_end'   => $request->input('window_time_end'),
            ]);

            $nurseMessage = "Nurse {$user->display_name} will work for";
        }

        $dayName = clhDayOfWeekToDayName($window->day_of_week);

        $from = Carbon::parse($window->window_time_start);
        $to = Carbon::parse($window->window_time_end);

        $message = "$nurseMessage {$request->input('work_hours')} hours on $dayName between {$from->format('h:i T')} to {$to->format('h:i T')}";

        sendSlackMessage('#carecoachscheduling', $message);

        $workHours = $this->workHours->updateOrCreate([
            'workhourable_type' => Nurse::class,
            'workhourable_id'   => $nurseInfoId,
        ], [
            strtolower(clhDayOfWeekToDayName($request->input('day_of_week'))) => $request->input('work_hours'),
        ]);

        return redirect()->back();
    }

    public function destroy($windowId)
    {
        $window = $this->nurseContactWindows
            ->find($windowId);

        if (!$window) {
            $errors['window'] = 'This window does not exist.';

            return redirect()->route('care.center.work.schedule.index')
                ->withErrors($errors)
                ->withInput();
        }

        if (!auth()->user()->hasRole('administrator')) {
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

        if (!$holiday) {
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

    public function getAllNurseSchedules()
    {
        $data = User::ofType('care-center')
            ->with('nurseInfo.windows')
            ->whereHas('nurseInfo', function ($q) {
                $q->where('status', 'active');
            })
            ->get()
            ->sortBy('first_name');

        $tzAbbr = auth()->user()->timezone_abbr ?? 'EDT';

        return view('admin.nurse.schedules.index', compact(['data', 'tzAbbr', 'workHours']));
    }

    public function updateDailyHours(Request $request, $id)
    {
        $workHours = WorkHours::find($id);
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
