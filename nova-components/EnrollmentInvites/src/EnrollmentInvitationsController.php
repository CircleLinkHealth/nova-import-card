<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Circlelinkhealth\EnrollmentInvites;

use App\Jobs\SelfEnrollmentEnrollees;
use App\Jobs\SelfEnrollmentUnreachablePatients;
use Laravel\Nova\Http\Requests\NovaRequest;

class EnrollmentInvitationsController
{
    public function handle(NovaRequest $novaRequest)
    {
        if (boolval($novaRequest->input('is_patient'))) {
            SelfEnrollmentUnreachablePatients::dispatch(
                null,
                intval($novaRequest->input('amount')),
                intval($novaRequest->input('practice_id'))
            );

            return $this->response();
        }

        SelfEnrollmentEnrollees::dispatch(
            null,
            $novaRequest->input('color'),
            intval($novaRequest->input('amount')),
            intval($novaRequest->input('practice_id'))
        );

        return $this->response();
    }

    public function response()
    {
        return response()->json(
            [
                'message' => 'Invitations sent',
            ],
            200
        );
    }
}
