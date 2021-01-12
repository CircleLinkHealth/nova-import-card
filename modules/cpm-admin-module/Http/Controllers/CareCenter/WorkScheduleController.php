<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Http\Controllers\CareCenter;

use Carbon\Carbon;
use CircleLinkHealth\Core\Traits\ApiReturnHelpers;
use CircleLinkHealth\Customer\Entities\Holiday;
use CircleLinkHealth\Customer\Entities\NurseContactWindow;
use CircleLinkHealth\Customer\Entities\WorkHours;
use CircleLinkHealth\Customer\Services\NurseCalendarService;
use CircleLinkHealth\Customer\Traits\ValidatesWorkScheduleCalendar;
use Illuminate\Routing\Controller;

class WorkScheduleController extends Controller
{
    use ApiReturnHelpers;
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

    public function showAllNurseScheduleForAdmin()
    {
        $authData = $this->fullCalendarService->getAuthData();
        $today    = Carbon::parse(now())->toDateString();

        return view('cpm-admin::admin.nurse.schedules.index', compact('authData', 'today'));
    }
}
