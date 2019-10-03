<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\CareCenter;

use App\FullCalendar\FullCalendarService;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Holiday;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\NurseContactWindow;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Entities\WorkHours;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Validator;

class WorkScheduleController extends Controller
{
    protected $fullCalendarService;
    protected $holiday;
    protected $nextWeekEnd;
    protected $nextWeekStart;
    protected $nurseContactWindows;
    protected $today;
    protected $workHours;

    /**
     * WorkScheduleController constructor.
     *
     * @param NurseContactWindow  $nurseContactWindow
     * @param Holiday             $holiday
     * @param WorkHours           $workHours
     * @param FullCalendarService $fullCalendarService
     */
    public function __construct(
        NurseContactWindow $nurseContactWindow,
        Holiday $holiday,
        WorkHours $workHours,
        FullCalendarService $fullCalendarService
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
        $this->fullCalendarService = $fullCalendarService;
    }

    /**
     * @param $windowId
     *
     * @return \Illuminate\Http\RedirectResponse|JsonResponse
     */
    public function destroy($windowId)
    {
        $window = $this->nurseContactWindows
            ->find($windowId);

        if ( ! $window) {
            $errors['window'] = 'This window does not exist.';

//            return redirect()->route('care.center.work.schedule.index')
//                ->withErrors($errors)
//                ->withInput();
            return response()->json([
                'errors'    => 'Validation Failed',
                'validator' => $errors,
            ], 422);
        }

        if ( ! auth()->user()->isAdmin()) {
            if ($window->nurse_info_id != auth()->user()->nurseInfo->id) {
                $errors['window'] = 'This window does not belong to you.';

                if (request()->expectsJson()) {
                    return response()->json([
                        'errors' => $errors,
                    ], 422);
                }

                return redirect()->route('care.center.work.schedule.index')
                    ->withErrors($errors)
                    ->withInput();
            }
        }

        $window->forceDelete();

        if (request()->expectsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Window has been deleted',
            ], 200);
        }

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

        if (request()->expectsJson()) {
            return response()->json([
                'message'   => 'Holiday Deleted',
                'holidayId' => $holidayId,
            ], 200);
        }

        return redirect()->back();
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getAllNurseSchedules()
    {
        $startOfThisYear = Carbon::parse(now())->startOfYear()->subMonth(2)->startOfWeek()->toDateString();
        //subDay is needed in order to work properly in front-end
        $startOfThisWeek = Carbon::parse(now())->startOfWeek()->subDay(1)->toDateString();
        $endOfThisWeek   = Carbon::parse(now())->endOfWeek()->subDay(1)->toDateString();

        $nurses          = $this->getNursesWithSchedule();
        $calendarData    = $this->fullCalendarService->prepareDataForCalendar($nurses, $startOfThisYear);
        $tzAbbr          = auth()->user()->timezone_abbr ?? 'EDT';
        $dataForDropdown = $this->fullCalendarService->getDataForDropdown($nurses);

        return view(
            'admin.nurse.schedules.index',
            compact(
                'nurses',
                'calendarData',
                'dataForDropdown',
                'startOfThisYear',
                'endOfThisWeek',
                'startOfThisWeek'
            )
        );
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|Response
     */
    public function getHolidays()
    {
        $nurses = $this->getNursesWithSchedule();

        if ( ! $nurses) {
            return response()->json(['errors' => 'Nurses not found'], 400);
        }
        $holidays = $this->fullCalendarService->getHolidays($nurses)->toArray();

        return response()->json([
            'success'  => true,
            'holidays' => $holidays,
        ], 200);
    }

    public function getNursesWithSchedule()
    {
        $workScheduleData = [];
        User::ofType('care-center')
            ->with('nurseInfo.windows', 'nurseInfo.holidays')
            ->whereHas('nurseInfo', function ($q) {
                $q->where('status', 'active');
            })
            // ->whereHas('nurseInfo.windows')
            ->chunk(100, function ($nurses) use (&$workScheduleData) {
                $workScheduleData[] = $nurses;
            });

        return $workScheduleData[0];
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
        $dataRequest = $request->all();

        if ( ! array_key_exists('day_of_week', $dataRequest)) {
            $inputDate                  = $dataRequest['date'];
            $dataRequest['day_of_week'] = carbonToClhDayOfWeek(Carbon::parse($inputDate)->dayOfWeek);
//            $dataRequest['date']        = Carbon::parse($inputDate)->startOfWeek()->format('Y-m-d');
        }

        $workScheduleData = $dataRequest;

        $isAdmin     = auth()->user()->isAdmin();
        $nurseInfoId = $isAdmin
            ? $workScheduleData['nurse_info_id']
            : auth()->user()->nurseInfo->id;

        if ( ! $nurseInfoId) {
            $nurseInfoId = auth()->user()->nurseInfo->id;
        }

        $validator = Validator::make($workScheduleData, [
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
                $workScheduleData['window_time_start'],
            ],
            [
                'window_time_start',
                '<=',
                $workScheduleData['window_time_end'],
            ],
            [
                'day_of_week',
                '=',
                $workScheduleData['day_of_week'],
            ],
        ])->first();

        $hoursSum = NurseContactWindow::where([
            ['nurse_info_id', '=', $nurseInfoId],
            ['day_of_week', '=', $workScheduleData['day_of_week']],
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
                $workScheduleData['window_time_end']
            )->diffInHours(Carbon::createFromFormat(
                'H:i',
                $workScheduleData['window_time_start']
            ));

        $invalidWorkHoursNumber = false;

        if ($hoursSum < $workScheduleData['work_hours']) {
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

            if (request()->expectsJson()) {
                return response()->json([
                    'errors'    => 'Validation Failed',
                    'validator' => $validator->getMessageBag(),
                ], 422);
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
                'day_of_week'       => $workScheduleData['day_of_week'],
                'window_time_start' => $workScheduleData['window_time_start'],
                'window_time_end'   => $workScheduleData['window_time_end'],
            ]);

            $nurseUser = Nurse::find($nurseInfoId)->user;

            $dayName      = clhDayOfWeekToDayName($window->day_of_week);
            $nurseMessage = "Admin {$user->display_name} assigned Nurse {$nurseUser->display_name} to work for";
            $message      = "${nurseMessage} {$workScheduleData['work_hours']} hours on ${dayName} between {$window->range()->start->format('h:i A T')} to {$window->range()->end->format('h:i A T')}";
            sendSlackMessage('#carecoachscheduling', $message);
        } else {
            $user = auth()->user();

            $window = $user->nurseInfo->windows()->create([
                'date'              => Carbon::now()->format('Y-m-d'),
                'day_of_week'       => $workScheduleData['day_of_week'],
                'window_time_start' => $workScheduleData['window_time_start'],
                'window_time_end'   => $workScheduleData['window_time_end'],
            ]);
        }

        $workHours = $this->workHours->updateOrCreate([
            'workhourable_type' => Nurse::class,
            'workhourable_id'   => $nurseInfoId,
        ], [
            strtolower(clhDayOfWeekToDayName($workScheduleData['day_of_week'])) => $workScheduleData['work_hours'],
        ]);

        if (request()->expectsJson()) {
            return response()->json([
                'success'       => true,
                'window'        => $window,
                'scheduledData' => $workScheduleData,
                'workHours'     => $workHours,
            ], 200);
        }

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
