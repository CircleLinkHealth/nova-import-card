<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Circlelinkhealth\EnrollmentInvites;

use App\Jobs\SelfEnrollmentEnrollees;
use Laravel\Nova\Http\Requests\NovaRequest;

class EnrollmentInvitationsController
{
    public function handle(NovaRequest $novaRequest)
    {
        SelfEnrollmentEnrollees::dispatchNow(
            null,
            $novaRequest->input('color'),
            intval($novaRequest->input('amount')),
            intval($novaRequest->input('practice_id'))
        );
    }
}
