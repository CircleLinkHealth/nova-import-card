<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use App\Call;
use App\CallView;
use CircleLinkHealth\Customer\Entities\PatientContactWindow;

/**
 * Class PatientContactWindowObserver.
 */
class PatientContactWindowObserver
{
    /**
     * Listen for the PatientContactWindow created event.
     *
     * @param \CircleLinkHealth\Customer\Entities\PatientContactWindow $window
     */
    public function created(PatientContactWindow $window)
    {
        $this->updateScheduledActivitiesWithNewContactWindow($window);
    }

    /**
     * Listen for the NurseContactWindow deleted event.
     *
     * @param \CircleLinkHealth\Customer\Entities\PatientContactWindow $window
     */
    public function deleted(PatientContactWindow $window)
    {
    }

    /**
     * Listen for the PatientContactWindow update event.
     *
     * @param \CircleLinkHealth\Customer\Entities\PatientContactWindow $window
     */
    public function updated(PatientContactWindow $window)
    {
        $this->updateScheduledActivitiesWithNewContactWindow($window);
    }

    private function updateScheduledActivitiesWithNewContactWindow(PatientContactWindow $window)
    {
        // @var $auth \CircleLinkHealth\Customer\Entities\User
        $auth = auth()->user();

        if ( ! $auth) {
            return;
        }

        if ( ! $auth->isCareCoach()) {
            return;
        }

        $patientId = $window->patient_info->user_id;

        $callIds = CallView::where('nurse_id', '=', $auth->id)
            ->where('patient_id', '=', $patientId)
            ->where('status', '=', 'scheduled')
            /*
             * if only on calls / call backs
                           ->where(function ($q) {
                               $q->whereNull('type')
                                 ->orWhere('type', '=', 'call')
                                 ->orWhere('sub_type', '=', 'Call Back');
                           })
            */
            ->pluck('id');

        if (empty($callIds)) {
            return;
        }

        // todo: do we handle changes in contact days?

        Call::whereIn('id', $callIds)
            ->update([
                'window_start' => $window->window_time_start,
                'window_end'   => $window->window_time_end,
            ]);
    }
}
