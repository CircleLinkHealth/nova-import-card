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
        $windows = auth()->user()->nurseInfo->windows
            ->map(function ($window) {
                $window->deletable = $this->canAddNewWindow(Carbon::parse($window->date));

                return $window;
            })
            ->sortBy('date');

        return view('care-center.work-schedule', compact(['windows']));
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
                    "You can only add windows for next week after Wednesday at midnight.");
            }
        });

        if ($validator->fails()) {
            return redirect()->route('care.center.work.schedule.index')
                ->withErrors($validator)
                ->withInput();
        }

        $window = auth()->user()->nurseInfo->windows()->create([
            'date'              => $date->format('Y-m-d'),
            'day_of_week'       => carbonToClhDayOfWeek($date->dayOfWeek),
            'window_time_start' => $request->input('window_time_start'),
            'window_time_end'   => $request->input('window_time_end'),
        ]);

        return redirect()->route('care.center.work.schedule.index');
    }

    public function destroy($windowId)
    {
        $this->nurseContactWindows->destroy($windowId);

        return redirect()->route('care.center.work.schedule.index');
    }
}
