<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\CareCenter;

use App\FullCalendar\NurseCalendarService;
use App\Http\Controllers\Controller;
use App\Http\Requests\CalendarRange;
use App\Jobs\CreateCalendarRecurringEventsJob;
use App\Traits\ValidatesWorkScheduleCalendar;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Holiday;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\NurseContactWindow;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Entities\WorkHours;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;

class WorkScheduleController extends Controller
{
    use ValidatesWorkScheduleCalendar;

    protected $fullCalendarService;
    protected $holiday;
    protected $nextWeekEnd;
    protected $nextWeekStart;
    protected $nurseContactWindows;
    protected $today;
    protected $workHours;

    /**
     * WorkScheduleController constructor.
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

    public function calendarEvents(CalendarRange $request)
    {
        $startDate = Carbon::parse($request->input('start'))->toDateString();
        $endDate   = Carbon::parse($request->input('end'))->toDateString();
        $today     = Carbon::parse(now())->toDateString();
        $auth      = auth()->user();

        if ($auth->isAdmin()) {
            $nurses = $this->getActiveNurses();

            if (empty($nurses)) { //@todo:take care of this tanji-like code block
                return response()->json([
                    'errors'    => 'Validation Failed',
                    'validator' => 'There are currently no working Care Coaches in the database',
                ], 422);
            }

            $windowData      = $this->fullCalendarService->prepareCalendarDataForAllActiveNurses($nurses, $startDate, $endDate);
            $holidays        = $this->fullCalendarService->getHolidays($nurses, $startDate, $endDate)->toArray();
            $dataForDropdown = $this->fullCalendarService->getDataForDropdown($nurses);
        } elseif ($auth->isCareCoach()) {
            $windowData   = $this->calendarWorkEventsForAuthNurse($startDate, $endDate, $auth);
            $holidaysData = $auth->nurseInfo->nurseHolidaysWithCompanyHolidays($startDate, $endDate);
            //"Resetting" array keys using array_values().
            // Otherwise sometimes (when holidays are spreading into the beginning of next month) it becomes not iterable.
            $holidays        = array_values($this->fullCalendarService->prepareHolidaysData($holidaysData, $auth, $startDate, $endDate)->toArray());
            $dataForDropdown = '';
        }

        $tzAbbr = auth()->user()->timezone_abbr ?? 'EDT';

        $calendarData = [
            'workEvents'      => $windowData->toArray(),
            'dataForDropdown' => $dataForDropdown,
            'holidayEvents'   => $holidays,
            'today'           => $today,
        ];

        return response()->json([
            'success'      => true,
            'calendarData' => $calendarData,
        ], 200);
    }

    /**
     * @param $startDate
     * @param $endDate
     * @param $auth
     *
     * @return \Illuminate\Support\Collection
     */
    public function calendarWorkEventsForAuthNurse($startDate, $endDate, $auth)
    {
        $nurse   = $auth;
        $windows = $this->nurseContactWindows
            ->whereNurseInfoId($nurse->nurseInfo->id)
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->get()
            ->sortBy(function ($item) {
                return Carbon::createFromFormat(
                    'H:i:s',
                    "{$item->window_time_start}"
                );
            });

        return $this->fullCalendarService->prepareWorkDataForEachNurse($windows, $nurse);
    }

    /**
     * @return JsonResponse
     */
    public function dailyReportsForNurse()
    {
        $auth         = auth()->user();
        $dailyReports = $this->fullCalendarService->prepareDailyReportsForNurse($auth);

        return response()->json([
            'success'      => true,
            'dailyReports' => $dailyReports,
        ], 200);
    }

    /**
     * @param $windowId
     *
     * @return \Illuminate\Http\RedirectResponse|JsonResponse
     */
    public function destroy(Request $request, $windowId)
    {
        $deleteRecurringEvents = 'true' === $request->deleteRecurringEvents ? true : false;

        $window = $this->nurseContactWindows
            ->find($windowId);

        if (empty($window)) {
            return response()->json([
                'error' => 'window does not exists',
            ], 400);
        }

        $this->destroyWindowValidation($window);
        //  Delete
        $deleteRecurringEvents ? $this->multipleDelete($window) : $this->singleDelete($window);

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
            //@todo:return response here
            return redirect()->route('care.center.work.schedule.index')
                ->withErrors($errors)
                ->withInput();
        }

        if (auth()->user()->nurseInfo) {
            if ($holiday->nurse_info_id != auth()->user()->nurseInfo->id) {
                $errors['holiday'] = 'This holiday does not belong to you.';
                //@todo:return response here
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

    /**
     * @return Collection|mixed
     */
    public function getActiveNurses()
    {
        $workScheduleData = [];
        User::ofType('care-center')
            ->with('nurseInfo.windows', 'nurseInfo.holidays')
            ->whereHas('nurseInfo.windows')
            ->whereHas('nurseInfo', function ($q) {
                $q->where('status', 'active');
            })
            ->chunk(50, function ($nurses) use (&$workScheduleData) {
                $workScheduleData[] = $nurses;
            });

        return collect($workScheduleData)->flatten() ?? [];
    }

    /**
     * @param $nurseInfoId
     * @param $workScheduleData
     *
     * @return int|mixed
     */
    public function getHoursSum($nurseInfoId, $workScheduleData)
    {
        return NurseContactWindow::where([
            ['nurse_info_id', '=', $nurseInfoId],
            //            ['day_of_week', '=', $workScheduleData['day_of_week']],
            ['date', '=', $workScheduleData['date']],
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
    }

    public function getSelectedNurseCalendarData(Request $request)
    {
        $startDate = Carbon::parse($request->input('startDate'))->toDateString();
        $endDate   = Carbon::parse($startDate)->copy()->addMonths(2)->toDateString();

        $nurseInfoId = $request->input('nurseInfoId');
        $nurse       = Nurse::findOrFail($nurseInfoId)->user;

        $windows = $this->nurseContactWindows
            ->whereNurseInfoId($nurseInfoId)
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->get()
            ->sortBy(function ($item) {
                return Carbon::createFromFormat(
                    'H:i:s',
                    "{$item->window_time_start}"
                );
            });

        $eventsForSelectedNurse = $this->fullCalendarService->prepareWorkDataForEachNurse($windows, $nurse)->toArray();
        $holidaysData           = $nurse->nurseInfo->upcoming_holiday_dates->flatten(); // we only need the future holidays
        $holidays               = $this->fullCalendarService->prepareHolidaysData($holidaysData, $nurse, $startDate, $endDate)->toArray();

        return response()->json([
            'eventsForSelectedNurse' => array_merge($eventsForSelectedNurse, $holidays),
        ], 200);
    }

    public function index()
    {
        $authData = $this->fullCalendarService->getAuthData();
        $today    = Carbon::parse(now())->toDateString();
//        $tzAbbr = auth()->user()->timezone_abbr; // @todo: we need this ?  it wasnt used in view

        //I think time tracking submits along with the form, thus messing up sessions.
        //Temporary fix
        $disableTimeTracking = true; // @todo: we need this ?  it wasnt used in view

        return view('care-center.work-schedule', compact('authData', 'today'));
    }

    /**
     * @param $nurseInfoId
     * @param $workScheduleData
     */
    public function informSlackAdminSide($nurseInfoId, NurseContactWindow $window, $workScheduleData)
    {
        $user      = auth()->user();
        $nurseUser = Nurse::find($nurseInfoId)->user;
        $dayName   = clhDayOfWeekToDayName($window->day_of_week);

        $nurseMessage = "Admin {$user->display_name} assigned Nurse {$nurseUser->display_name} to work for";
        $message      = "${nurseMessage} {$workScheduleData['work_hours']} hours on ${dayName} between {$window->range()->start->format('h:i A T')} to {$window->range()->end->format('h:i A T')}";
        sendSlackMessage('#carecoachscheduling', $message);
    }

    /**
     * @param $window
     */
    public function informSlackNurseSide($window)
    {
        $auth     = auth()->user();
        $sentence = "Nurse {$auth->getFullName()} has just deleted the Window for";
        $sentence .= "{$window->dayName}, {$window->date->format('m-d-Y')} from {$window->range()->start->format('h:i A T')} to {$window->range()->end->format('h:i A T')}. View Schedule at ";
        $sentence .= route('get.admin.nurse.schedules');

        \sendSlackMessage('#carecoachscheduling', $sentence);
    }

    public function multipleDelete(NurseContactWindow $window)
    {
        $windowDate = Carbon::parse($window->date);
        $today      = now()->startOfDay();
        $tomorrow   = $today->addDay(1);
        $windows    = NurseContactWindow::where('nurse_info_id', $window->nurse_info_id)
            ->where('date', '>', $tomorrow)
            ->where('repeat_start', $window->repeat_start)
            ->get();

        foreach ($windows as $workWindow) {
            // Update Work Hours
            WorkHours::where('workhourable_id', $workWindow->nurse_info_id)
                ->where('work_week_start', '>=', $windowDate->copy()->startOfWeek())
                ->where('work_week_start', '<=', $workWindow->until)
                ->get()
                ->each(function ($week) use ($workWindow, $today, $tomorrow) {
                    /** @var WorkHours $week */
                    $dates = createWeekMap($week->work_week_start);
                    foreach ($dates as $date) {
                        $carbonDate = Carbon::parse($date);
                        if ($carbonDate->eq(Carbon::parse($workWindow->date))
                            && $carbonDate->gt($today)
                            && $carbonDate->gt($tomorrow)) {
                            $week->update(
                                [
                                    strtolower(clhDayOfWeekToDayName($carbonDate->dayOfWeek)) => 0,
                                ]
                            );
                        }
                    }
                });

            // Delete Window
            $workWindow->forceDelete();
        }

        $this->informSlackNurseSide($window);
    }

    /**
     * When admin creates single NOT repeated work window.
     *
     * @param $nurseInfoId
     * @param $workScheduleData
     *
     * @return \Illuminate\Database\Eloquent\Model|NurseContactWindow
     */
    public function saveAdminSingleWindow($nurseInfoId, $workScheduleData)
    {
        return $this->nurseContactWindows->create([
            'nurse_info_id'     => $nurseInfoId,
            'date'              => $workScheduleData['date'],
            'day_of_week'       => $workScheduleData['day_of_week'],
            'window_time_start' => $workScheduleData['window_time_start'],
            'window_time_end'   => $workScheduleData['window_time_end'],
            'repeat_frequency'  => $workScheduleData['repeat_freq'],
            'until'             => $workScheduleData['until'],
        ]);
    }

    /**
     * User nurse creates a NOT repeated work window.
     *
     * @param $workScheduleData
     *
     * @return mixed
     */
    public function saveNurseSingleWindow($workScheduleData)
    {
        $user = auth()->user();

        return $user->nurseInfo->windows()->create([
            'date'              => $workScheduleData['date'],
            'day_of_week'       => $workScheduleData['day_of_week'],
            'window_time_start' => $workScheduleData['window_time_start'],
            'window_time_end'   => $workScheduleData['window_time_end'],
            'repeat_frequency'  => $workScheduleData['repeat_freq'],
            'until'             => $workScheduleData['until'],
        ]);
    }

    /**
     * @param $nurseInfoId
     * @param mixed $workScheduleData
     */
    public function saveRecurringEvents(
        $nurseInfoId,
        bool $updateCollisions,
        \Illuminate\Contracts\Validation\Validator $validator,
        $workScheduleData
    ): JsonResponse {
        $recurringEventsToSave = $this->fullCalendarService->createRecurringEvents($nurseInfoId, $workScheduleData);
//        It should never happen. Same validation exist in client.We can also use it in the future to give the option to choose what to do with each event.
        $confirmationNeededEvents = $this->fullCalendarService->getEventsToAskConfirmation($recurringEventsToSave, $updateCollisions);
        if ( ! empty($confirmationNeededEvents) && ! $updateCollisions) {
            $collidingDates = $this->fullCalendarService->getCollidingDates($confirmationNeededEvents);

            return response()->json([
                'errors'    => 'Validation Failed',
                'validator' => $validator->getMessageBag()->add(
                    'error',
                    "This window is overlapping with an existing window in $collidingDates."
                ),
                'collidingEvents' => $confirmationNeededEvents,
            ], 422);
        }

        CreateCalendarRecurringEventsJob::dispatch($recurringEventsToSave, NurseContactWindow::class, $updateCollisions, $workScheduleData['work_hours'])->onQueue('low');

        return response()->json([
            'success' => true,
            'window'  => $recurringEventsToSave,
        ], 200);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showAllNurseScheduleForAdmin()
    {
        $authData = $this->fullCalendarService->getAuthData();
        $today    = Carbon::parse(now())->toDateString();

        return view('admin.nurse.schedules.index', compact('authData', 'today'));
    }

    /**
     * @param $window
     */
    public function singleDelete(NurseContactWindow $window)
    {
        $workScheduleData = [
            'day_of_week' => $window->day_of_week,
            'date'        => $window->date,
            'work_hours'  => 0,
        ];

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
                    if ($event->id === $window->id) {
                        $this->updateWorkHours($window->nurse_info_id, $workScheduleData);
                        $window->forceDelete();
                    } else {
                        $event->update(['repeat_frequency' => 'does_not_repeat']);
                    }
                }
            } else {
                $this->updateWorkHours($window->nurse_info_id, $workScheduleData);
                $window->forceDelete();
            }
        } else {
            $this->updateWorkHours($window->nurse_info_id, $workScheduleData);
            $window->forceDelete();
        }
    }

    public function store(Request $request)
    {
        $workScheduleData = $request->all();
        $nurseInfoId      = $workScheduleData['nurse_info_id'];
        if ( ! array_key_exists('day_of_week', $workScheduleData)) {
            $inputDate                       = $workScheduleData['date'];
            $workScheduleData['day_of_week'] = carbonToClhDayOfWeek(Carbon::parse($inputDate)->dayOfWeek);
        }

        $validator = $this->validatorScheduleData($workScheduleData);

        $updateCollisions = null === $workScheduleData['updateCollisions'] ? false : $workScheduleData['updateCollisions'];
        $isAdmin          = auth()->user()->isAdmin();
        $nurseInfoId      = $isAdmin ? $nurseInfoId : auth()->user()->nurseInfo->id;

        if ($validator->fails()) {
            return response()->json([
                'errors'    => 'Validation Failed',
                'validator' => $validator->errors(),
            ], 422);
        }

        $windowExists              = $this->windowsExistsValidator($workScheduleData, $updateCollisions);
        $workHoursRangeSum         = $this->getHoursSum($nurseInfoId, $workScheduleData);
        $invalidWorkHoursCommitted = $this->invalidWorkHoursValidator($workHoursRangeSum, $workScheduleData['work_hours']);
        $holidayExists             = Holiday::where('nurse_info_id', $nurseInfoId)->where('date', $workScheduleData['date'])->exists();

        if ($windowExists || $holidayExists || $invalidWorkHoursCommitted || ('does_not_repeat' !== $workScheduleData['repeat_freq'] && null === $workScheduleData['until'])) {
            $validationResponse = $this->returnValidationResponse($windowExists, $validator, $invalidWorkHoursCommitted, $workScheduleData, $holidayExists);

            return response()->json([
                'errors'    => 'Validation Failed',
                'validator' => $validationResponse->getMessageBag(),
            ], 422);
        }

        if ($isAdmin) {
            if ('does_not_repeat' !== $workScheduleData['repeat_freq']) {
                return $this->saveRecurringEvents($nurseInfoId, $updateCollisions, $validator, $workScheduleData);
            }
            $window = $this->saveAdminSingleWindow($nurseInfoId, $workScheduleData);
            $this->informSlackAdminSide($nurseInfoId, $window, $workScheduleData);
        } else {
            if ('does_not_repeat' !== $workScheduleData['repeat_freq']) {
                return $this->saveRecurringEvents($nurseInfoId, $updateCollisions, $validator, $workScheduleData);
            }
            $window = $this->saveNurseSingleWindow($workScheduleData);
        }
        // Update Work Hours.
        $workHours = $this->updateWorkHours($nurseInfoId, $workScheduleData);

        if (request()->expectsJson()) {
            return response()->json([
                'success'       => true,
                'window'        => $window,
                'scheduledData' => $workScheduleData,
                'workHours'     => $workHours,
            ], 200);
        }
    }

    public function storeHoliday(Request $request)
    {
        $user = auth()->user();

        $request->replace([
            'holiday' => Carbon::parse($request->input('holiday'))->toDateTimeString(),
        ]);
        $date = Carbon::parse($request->input('holiday'))->toDateString();

        $validator = Validator::make($request->all(), [
            'holiday' => [
                Rule::unique('holidays', 'date')->where(function ($query) use ($user) {
                    $query->where('nurse_info_id', $user->nurseInfo->id);
                }),
                'required',
                'date',
            ],
        ]);
        $workEventExistsOnSameDate = $user->nurseInfo->windows()->where('date', $date)->exists();
        if ($workEventExistsOnSameDate) {
            $validator->getMessageBag()->add(
                'error',
                'This day already has a scheduled event. 
                If you wish to change your schedule, please remove the existing event first.'
            );

            return response()->json([
                'errors'    => 'Validation Failed',
                'validator' => $validator->errors(),
            ], 422);
        }

        if ($validator->fails()) {
            return response()->json([
                'errors'    => 'Validation Failed',
                'validator' => $validator->errors(),
            ], 422);
        }

        $holiday = $user->nurseInfo->holidays()->create([
            'date' => Carbon::parse($request->input('holiday'))->format('Y-m-d'),
        ]);

        return redirect()->back();
    }

    /**
     * @param $nurseInfoId
     *
     * @return \Illuminate\Database\Eloquent\Model|WorkHours
     */
    public function updateWorkHours($nurseInfoId, array $workScheduleData)
    {
        $workWeekStart = Carbon::parse($workScheduleData['date'])->copy()->startOfWeek();

        return $this->workHours->updateOrCreate(
            [
                'workhourable_type' => Nurse::class,
                'workhourable_id'   => $nurseInfoId,
                'work_week_start'   => Carbon::parse($workWeekStart)->toDateString(),
            ],
            [
                strtolower(clhDayOfWeekToDayName($workScheduleData['day_of_week'])) => $workScheduleData['work_hours'],
            ]
        );
    }
}
