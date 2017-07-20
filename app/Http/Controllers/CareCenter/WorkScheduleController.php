<?php

namespace App\Http\Controllers\CareCenter;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
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

    public function __construct(
        NurseContactWindow $nurseContactWindow,
        Holiday $holiday
    ) {
        $this->nextWeekStart = Carbon::parse('this sunday')->copy();
        $this->nextWeekEnd = Carbon::parse('next sunday')
            ->endOfDay()
            ->addWeek(1)
            ->copy();
        $this->nurseContactWindows = $nurseContactWindow;
        $this->holiday = $holiday;
        $this->today = Carbon::today()->copy();
    }

    public function index()
    {
        $windows = $this->nurseContactWindows
            ->whereNurseInfoId(auth()->user()->nurseInfo->id)
            ->get()
            ->sortBy(function ($item) {
                return Carbon::createFromFormat('Y-m-d H:i:s',
                    "{$item->date->format('Y-m-d')} $item->window_time_start");
            });

        $holidays = auth()->user()->nurseInfo->upcoming_holiday_dates;
        $holidaysThisWeek = auth()->user()->nurseInfo->holidays_this_week;

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

        $holiday = auth()->user()->nurseInfo->holidays()->create([
            'date' => Carbon::parse($request->input('holiday'))->format('Y-m-d'),
        ]);

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

        if ($validator->fails() || $windowExists) {
            $validator->getMessageBag()->add('window_time_start',
                'This window is overlapping with an already existing window.');

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        if ($isAdmin) {
            $this->nurseContactWindows->create([
                'nurse_info_id'     => $nurseInfoId,
                'date'              => Carbon::now()->format('Y-m-d'),
                'day_of_week'       => $request->input('day_of_week'),
                'window_time_start' => $request->input('window_time_start'),
                'window_time_end'   => $request->input('window_time_end'),
            ]);
        } else {
            $window = auth()->user()->nurseInfo->windows()->create([
                'date'              => Carbon::now()->format('Y-m-d'),
                'day_of_week'       => $request->input('day_of_week'),
                'window_time_start' => $request->input('window_time_start'),
                'window_time_end'   => $request->input('window_time_end'),
            ]);
        }



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

        if ($holiday->nurse_info_id != auth()->user()->nurseInfo->id) {
            $errors['holiday'] = 'This holiday does not belong to you.';

            return redirect()->route('care.center.work.schedule.index')
                ->withErrors($errors)
                ->withInput();
        }

        $holiday->forceDelete();

        return redirect()->route('care.center.work.schedule.index');
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

        $tzAbbr = auth()->user()->timezone_abbr;

        return view('admin.nurse.schedules.index', compact(['data', 'tzAbbr']));
    }

    protected function canAddNewWindow(Carbon $date)
    {
        return ($date->gt($this->nextWeekStart) && $this->today->dayOfWeek < 4)
            || $date->gt($this->nextWeekEnd);
    }
}
