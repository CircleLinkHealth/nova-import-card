<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Circlelinkhealth\EnrollmentInvites;

use App\SelfEnrollment\Domain\InviteUnreachablePatients;
use App\SelfEnrollment\Jobs\DispatchSelfEnrollmentDomainAction;
use App\SelfEnrollment\Jobs\InvitePracticeEnrollees;
use Laravel\Nova\Http\Requests\NovaRequest;

class EnrollmentInvitationsController
{
    public function handle(NovaRequest $novaRequest)
    {
        $this->validation($novaRequest);

        if (boolval($novaRequest->input('is_patient'))) {
            DispatchSelfEnrollmentDomainAction::dispatch(
                new InviteUnreachablePatients(
                    $novaRequest->input('practice_id'),
                    $novaRequest->input('amount')
                )
            );

            return $this->response();
        }

        InvitePracticeEnrollees::dispatch(
            intval($novaRequest->input('amount')),
            intval($novaRequest->input('practice_id')),
            $novaRequest->input('color')
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

    private function validation(NovaRequest $novaRequest)
    {
        if (empty($novaRequest->input('amount'))) {
            return response()->json(
                [
                    'message' => 'Invitations number to be send is required',
                ],
                404
            );
        }
    }
}
