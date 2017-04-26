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
            ->where('date', '>=', $this->today->format('Y-m-d'))
            ->whereNurseInfoId(auth()->user()->nurseInfo->id)
            ->get()
            ->sortBy(function ($item) {
                return Carbon::createFromFormat('Y-m-d H:i:s',
                    "{$item->date->format('Y-m-d')} $item->window_time_start");
            });

        $holidays = $this->holiday
            ->where('date', '>=', $this->today->format('Y-m-d'))
            ->whereNurseInfoId(auth()->user()->nurseInfo->id)
            ->get()
            ->sortBy(function ($item) {
                return Carbon::createFromFormat('Y-m-d',
                    "{$item->date->format('Y-m-d')}");
            });

        $tzAbbr = auth()->user()->timezone
            ? Carbon::now(auth()->user()->timezone)->format('T')
            : false;

        //I think time tracking submits along with the form, thus messing up sessions.
        //Temporary fix
        $disableTimeTracking = true;

        return view('care-center.work-schedule', compact([
            'disableTimeTracking',
            'holidays',
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
            'holiday' => "required|date|after:tomorrow",
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
        $validator = Validator::make($request->all(), [
            'date'              => "required",
            'window_time_start' => 'required|date_format:H:i',
            'window_time_end'   => 'required|date_format:H:i|after:window_time_start',
        ]);


        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $window = auth()->user()->nurseInfo->windows()->create([
            'date'              => Carbon::now()->format('Y-m-d'),
            'day_of_week'       => $request->input('date'),
            'window_time_start' => $request->input('window_time_start'),
            'window_time_end'   => $request->input('window_time_end'),
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

        if ($window->nurse_info_id != auth()->user()->nurseInfo->id) {
            $errors['window'] = 'This window does not belong to you.';

            return redirect()->route('care.center.work.schedule.index')
                ->withErrors($errors)
                ->withInput();
        }

        if (!$this->canAddNewWindow(Carbon::parse($window->date))) {
            $errors['window'] = 'You cannot delete this window anymore.';

            return redirect()->route('care.center.work.schedule.index')
                ->withErrors($errors)
                ->withInput();
        }

        $window->forceDelete();

        return redirect()->route('care.center.work.schedule.index');
    }

    protected function canAddNewWindow(Carbon $date)
    {
        return ($date->gt($this->nextWeekStart) && $this->today->dayOfWeek < 4)
            || $date->gt($this->nextWeekEnd);
    }

    public function getAllNurseSchedules()
    {
        $data = User::ofType('care-center')
            ->with('nurseInfo.upcomingWindows')
            ->whereHas('nurseInfo', function ($q) {
                $q->where('status', 'active');
            })
            ->get()
            ->sortBy('first_name');

        return view('admin.nurse.schedules.index', compact('data'));
    }

    public function patchAdminEditWindow(
        $id,
        Request $request
    ) {
        $date = Carbon::createFromFormat('m-d-Y', $request->input('date'))->copy();

        $this->nurseContactWindows->whereId($id)
            ->update([
                'date'              => $date->format('Y-m-d'),
                'day_of_week'       => carbonToClhDayOfWeek($date->dayOfWeek),
                'window_time_start' => $request->input('window_time_start'),
                'window_time_end'   => $request->input('window_time_end'),
            ]);

        return redirect()->back();
    }

    public function postAdminStoreWindow(
        $id,
        Request $request
    ) {
        $date = Carbon::createFromFormat('m-d-Y', $request->input('date'))->copy();

        $this->nurseContactWindows->create([
            'nurse_info_id'     => $id,
            'date'              => $date->format('Y-m-d'),
            'day_of_week'       => carbonToClhDayOfWeek($date->dayOfWeek),
            'window_time_start' => $request->input('window_time_start'),
            'window_time_end'   => $request->input('window_time_end'),
        ]);

        return redirect()->back();
    }
}
