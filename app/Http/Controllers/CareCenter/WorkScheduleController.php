<?php

namespace App\Http\Controllers\CareCenter;

use App\Http\Controllers\Controller;
use App\NurseContactWindow;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Validator;

class WorkScheduleController extends Controller
{
    protected $nextWeekEnd;
    protected $nextWeekStart;
    protected $nurseContactWindows;
    protected $today;

    public function __construct(NurseContactWindow $nurseContactWindow)
    {
        $this->nextWeekStart = Carbon::parse('this sunday')->copy();
        $this->nextWeekEnd = Carbon::parse('next sunday')
            ->endOfDay()
            ->addWeek(1)
            ->copy();
        $this->nurseContactWindows = $nurseContactWindow;
        $this->today = Carbon::today()->copy();
    }

    public function index()
    {
        $windows = $this->nurseContactWindows
            ->where('date', '>=', $this->today->format('Y-m-d'))
            ->whereNurseInfoId(auth()->user()->nurseInfo->id)
            ->get()
            ->map(function ($window) {
                $window->deletable = $this->canAddNewWindow(Carbon::parse($window->date));

                return $window;
            })
            ->sortBy(function ($item) {
                return Carbon::createFromFormat('Y-m-d H:i:s',
                    "{$item->date->format('Y-m-d')} $item->window_time_start");
            });

        $tzAbbr = auth()->user()->timezone
            ? Carbon::now(auth()->user()->timezone)->format('T')
            : false;

        return view('care-center.work-schedule', compact([
            'windows',
            'tzAbbr',
        ]));
    }

    protected function canAddNewWindow(Carbon $date)
    {
        return ($date->gt($this->nextWeekStart) && $this->today->dayOfWeek < 4)
        || $date->gt($this->nextWeekEnd);
    }

    public function store(Request $request)
    {
        $deadline = $this->nextWeekStart->format('m-d-Y');

        $validator = Validator::make($request->all(), [
            'date'              => "required|date_format:m-d-Y|after:$deadline",
            'window_time_start' => 'required|date_format:H:i',
            'window_time_end'   => 'required|date_format:H:i|after:window_time_start',
        ]);

        $date = Carbon::createFromFormat('m-d-Y', $request->input('date'))->copy();

        $validator->after(function ($validator) use
        (
            $date
        ) {
            if (!$this->canAddNewWindow($date)) {
                $validator->errors()->add('date',
                    "The windows was not created because it is out of the allowed date range.");
            }
            if ($date->format('Y-m-d') == '0000-00-00') {
                $validator->errors()->add('date',
                    "The date given is invalid.");
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $window = auth()->user()->nurseInfo->windows()->create([
            'date'              => $date->format('Y-m-d'),
            'day_of_week'       => carbonToClhDayOfWeek($date->dayOfWeek),
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

    public function getAllNurseSchedules()
    {
        $windows = $this->nurseContactWindows
            ->getScheduleForAllNurses()
            ->sortBy(function ($item) {
                return Carbon::createFromFormat('Y-m-d H:i:s',
                    "{$item->date->format('Y-m-d')} $item->window_time_start");
            })
            ->groupBy('nurse_info_id');

        return view('admin.nurse.schedules.index', compact('windows'));
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
