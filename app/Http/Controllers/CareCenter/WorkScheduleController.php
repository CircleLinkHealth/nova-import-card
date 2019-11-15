<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\CareCenter;

use App\FullCalendar\NurseCalendarService;
use App\Http\Controllers\Controller;
use App\Jobs\CreateCalendarRecurringEventsJob;
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
     * @param NurseContactWindow   $nurseContactWindow
     * @param Holiday              $holiday
     * @param WorkHours            $workHours
     * @param NurseCalendarService $fullCalendarService
     */
    public function __construct(
        NurseContactWindow $nurseContactWindow,
        Holiday $holiday,
        WorkHours $workHours,
        NurseCalendarService $fullCalendarService
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
     * @param Request $request
     * @param $windowId
     *
     * @return \Illuminate\Http\RedirectResponse|JsonResponse
     */
    public function destroy(Request $request, $windowId)
    {
        $deleteRecurringEvents = 'true' === $request->deleteRecurringEvents ? true : false;

        $window = $this->nurseContactWindows
            ->find($windowId);

        $this->destroyWindowValidation($window);

        $delete = $deleteRecurringEvents ? $this->multipleDelete($window) : $this->singleDelete($window);

        $message = $deleteRecurringEvents ? 'All repeated events have been deleted' : 'Event has been deleted';

        return response()->json([
            'status'  => 'success',
            'message' => $message,
        ], 200);
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
     * @param $window
     *
     * @return JsonResponse
     */
    public function destroyWindowValidation($window)
    {
        if ( ! $window) {
            $errors['window'] = 'This window does not exist.';

            return response()->json([
                'errors'    => 'Validation Failed',
                'validator' => $errors,
            ], 422);
        }

        if ( ! auth()->user()->isAdmin()) {
            if ($window->nurse_info_id != auth()->user()->nurseInfo->id) {
                $errors['window'] = 'This window does not belong to you.';

                return response()->json([
                    'errors' => $errors,
                ], 422);
            }
        }
    }

    public function getActiveNurses()
    {
        $workScheduleData = [];
        User::ofType('care-center')
            ->with('nurseInfo.windows', 'nurseInfo.holidays')
            ->whereHas('nurseInfo', function ($q) {
                $q->where('status', 'active');
            })
            ->chunk(100, function ($nurses) use (&$workScheduleData) {
                $workScheduleData[] = $nurses;
            });

        return $workScheduleData[0];
    }

    public function getCalendarData()
    {
        $today        = Carbon::parse(now())->toDateString();
        $startOfMonth = Carbon::parse($today)->startOfMonth()->copy()->toDateString();
        $endOfMonth   = Carbon::parse($today)->endOfMonth()->copy()->toDateString();
        $endOfYear    = Carbon::parse($today)->endOfYear()->copy()->toDateString();

        if (auth()->user()->isAdmin()) {
            $nurses          = $this->getActiveNurses();
            $windowData      = $this->fullCalendarService->prepareCalendarDataForAllActiveNurses($nurses);
            $dataForDropdown = $this->fullCalendarService->getDataForDropdown($nurses);
        } else {
            $windowData      = $this->getCalendarDataForAuthNurse();
            $dataForDropdown = '';
        }

        $tzAbbr = auth()->user()->timezone_abbr ?? 'EDT';

        $calendarData = [
            'workEvents'      => $windowData,
            'dataForDropdown' => $dataForDropdown,
            'today'           => $today,
            'startOfMonth'    => $startOfMonth,
            'endOfMonth'      => $endOfMonth,
            'endOfYear'       => $endOfYear,
        ];

        return response()->json([
            'success'      => true,
            'calendarData' => $calendarData,
        ], 200);
    }

    public function getCalendarDataForAuthNurse()
    {
        $nurse       = auth()->user();
        $authIsAdmin = false;
        $windows     = $this->nurseContactWindows
            ->whereNurseInfoId($nurse->nurseInfo->id)
            ->get()
            ->sortBy(function ($item) {
                return Carbon::createFromFormat(
                    'H:i:s',
                    "{$item->window_time_start}"
                );
            });

        return $this->fullCalendarService->prepareDataForEachNurse($windows, $nurse);
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|Response
     */
    public function getHolidays()
    {
        $nurses = $this->getActiveNurses();

        if ( ! $nurses) {
            return response()->json(['errors' => 'Nurses not found'], 400);
        }
        $holidays = $this->fullCalendarService->getHolidays($nurses)->toArray();

        return response()->json([
            'success'  => true,
            'holidays' => $holidays,
        ], 200);
    }

    public function index()
    {
        $authIsAdmin = json_encode(false);

//        $holidays = $nurse->nurseInfo->upcoming_holiday_dates;
//        $holidaysThisWeek = $nurse->nurseInfo->holidays_this_week;

//        $tzAbbr = auth()->user()->timezone_abbr;

        //I think time tracking submits along with the form, thus messing up sessions.
        //Temporary fix
        $disableTimeTracking = true; // @todo: we need this
        return view('care-center.work-schedule', compact('authIsAdmin'));
//        return view('care-center.work-schedule', compact([
//            'disableTimeTracking',
//            'holidays',
//            'holidaysThisWeek',
//            'windows',
//            'tzAbbr',
//            'nurse',
//            'authIsAdmin'
//        ]));
    }

    public function multipleDelete($window)
    {
        $this->nurseContactWindows
            ->where('nurse_info_id', $window->nurse_info_id)
            ->where('repeat_start', $window->repeat_start)
            ->forceDelete();
    }

    /**
     * @param $eventDate
     * @param $nurseInfoId
     * @param $windowTimeStart
     * @param $windowTimeEnd
     * @param $repeatFrequency
     * @param $windowRepeatUntil
     * @param bool                                       $updateCollisions
     * @param \Illuminate\Contracts\Validation\Validator $validator
     *
     * @return JsonResponse
     */
    public function saveRecurringEvents($eventDate, $nurseInfoId, $windowTimeStart, $windowTimeEnd, $repeatFrequency, $windowRepeatUntil, bool $updateCollisions, \Illuminate\Contracts\Validation\Validator $validator): JsonResponse
    {
        $recurringEventsToSave    = $this->fullCalendarService->createRecurringEvents($eventDate, $nurseInfoId, $windowTimeStart, $windowTimeEnd, $repeatFrequency, $windowRepeatUntil);
        $confirmationNeededEvents = $this->fullCalendarService->getEventsToAskConfirmation($recurringEventsToSave);

        if ( ! empty($confirmationNeededEvents) && ! $updateCollisions) {
            $collidingDates = $this->fullCalendarService->getCollidingDates($confirmationNeededEvents);

            return response()->json([
                'errors'    => 'Validation Failed',
                'validator' => $validator->getMessageBag()->add(
                    'window_time_start',
                    "This window is overlapping with an already existing window in $collidingDates."
                ),
                'collidingEvents' => $confirmationNeededEvents,
            ], 422);
        }

        CreateCalendarRecurringEventsJob::dispatch($recurringEventsToSave, NurseContactWindow::class, $updateCollisions)->onQueue('low');

        return response()->json([
            'success' => true,
            'window'  => $recurringEventsToSave,
        ], 200);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showAllNurseSchedules()
    {
        $authIsAdmin = auth()->user()->isAdmin();
        $authUserId  = auth()->id();

        return view('admin.nurse.schedules.index', compact('authIsAdmin', 'authUserId'));
    }

    /**
     * @param $window
     */
    public function singleDelete(NurseContactWindow $window)
    {
        if ('does_not_repeat' !== $window->repeat_frequency) {
            $windowsCount = $this->nurseContactWindows
                ->where('nurse_info_id', $window->nurse_info_id)
                ->where('repeat_start', $window->repeat_start)
                ->count();

            if (2 === $windowsCount) {
                $lastTwoEvents = $this->nurseContactWindows
                    ->where('nurse_info_id', $window->nurse_info_id)
                    ->where('repeat_start', $window->repeat_start)
                    ->get();
                //Update last event of repeated events frequency to 'does_not_repeat'.
                foreach ($lastTwoEvents as $event) {
                    $event->id === $window->id ?
                        $window->forceDelete()
                        : $event->update(['repeat_frequency' => 'does_not_repeat']);
                }
            } else {
                $window->forceDelete();
            }
        } else {
            $window->forceDelete();
        }
    }

    public function store(Request $request)
    {
        $dataRequest = $request->all();

        if ( ! array_key_exists('day_of_week', $dataRequest)) {
            $inputDate                  = $dataRequest['date'];
            $dataRequest['day_of_week'] = carbonToClhDayOfWeek(Carbon::parse($inputDate)->dayOfWeek);
        }

        $workScheduleData = $dataRequest;

        $eventDate         = $workScheduleData['date'];
        $nurseInfoId       = $workScheduleData['nurse_info_id'];
        $windowTimeStart   = $workScheduleData['window_time_start'];
        $windowTimeEnd     = $workScheduleData['window_time_end'];
        $repeatFrequency   = $workScheduleData['repeat_freq']; //@todo: replace with $vars
        $windowDayOfWeek   = $workScheduleData['day_of_week'];
        $windowRepeatUntil = $workScheduleData['until'];
        $updateCollisions  = null === $workScheduleData['updateCollisions'] ? false : $workScheduleData['updateCollisions'];

        $isAdmin     = auth()->user()->isAdmin();
        $nurseInfoId = $isAdmin
            ? $nurseInfoId
            : auth()->user()->nurseInfo->id;

        if ( ! $nurseInfoId) {
            $nurseInfoId = auth()->user()->nurseInfo->id;
        }

        $validator = Validator::make($workScheduleData, [
            'day_of_week'       => 'required',
            'window_time_start' => 'required|date_format:H:i',
            'window_time_end'   => 'required|date_format:H:i|after:window_time_start',
        ]);

        $windowExists = $this->fullCalendarService->checkIfWindowsExists($nurseInfoId, $windowTimeStart, $windowTimeEnd, $eventDate);
        $hoursSum     = NurseContactWindow::where([
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
            if ('does_not_repeat' !== $repeatFrequency) {
                //@todo: lots of parameters. revisit this.
                return $this->saveRecurringEvents($eventDate, $nurseInfoId, $windowTimeStart, $windowTimeEnd, $repeatFrequency, $windowRepeatUntil, $updateCollisions, $validator);
            }
            $window = $this->nurseContactWindows->create([
                'nurse_info_id' => $nurseInfoId,
                'date'          => $dataRequest['date'],
                //                                'date'              => Carbon::now()->format('Y-m-d'),
                'day_of_week'       => $workScheduleData['day_of_week'],
                'window_time_start' => $workScheduleData['window_time_start'],
                'window_time_end'   => $workScheduleData['window_time_end'],
                'repeat_frequency'  => $workScheduleData['repeat_freq'],
                'until'             => $workScheduleData['until'],
            ]);

            $nurseUser = Nurse::find($nurseInfoId)->user;

            $dayName      = clhDayOfWeekToDayName($window->day_of_week);
            $nurseMessage = "Admin {$user->display_name} assigned Nurse {$nurseUser->display_name} to work for";
            //@todo: These mesaages needs to adapted.
            $message = "${nurseMessage} {$workScheduleData['work_hours']} hours on ${dayName} between {$window->range()->start->format('h:i A T')} to {$window->range()->end->format('h:i A T')}";
            sendSlackMessage('#carecoachscheduling', $message);
        } else {
            $user = auth()->user();
            if ('does_not_repeat' !== $repeatFrequency) {
                //@todo: lots of parameters. revisit this.
                return $this->saveRecurringEvents($eventDate, $nurseInfoId, $windowTimeStart, $windowTimeEnd, $repeatFrequency, $windowRepeatUntil, $updateCollisions, $validator);
            }
            $window = $user->nurseInfo->windows()->create([
                //                'date'              => Carbon::now()->format('Y-m-d'),
                'date'              => $dataRequest['date'],
                'day_of_week'       => $workScheduleData['day_of_week'],
                'window_time_start' => $workScheduleData['window_time_start'],
                'window_time_end'   => $workScheduleData['window_time_end'],
                'repeat_frequency'  => $workScheduleData['repeat_freq'],
                'until'             => $workScheduleData['until'],
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

//        return redirect()->back()->with(['editedNurseId' => $nurseInfoId]);
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
