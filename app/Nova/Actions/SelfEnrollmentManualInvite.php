<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Actions;

use App\EnrollmentInvitationsBatch;
use App\SelfEnrollment\Jobs\CreateSurveyOnlyUserFromEnrollee;
use App\SelfEnrollment\Jobs\SendInvitation;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class SelfEnrollmentManualInvite extends Action
{
    use InteractsWithQueue;
    use Queueable;

    public $name = 'Send Self Enrollment SMS/Email';

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
        ];
    }

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $models->each(function (Enrollee $enrollee) {
            if (is_null($enrollee->user_id)) {
                CreateSurveyOnlyUserFromEnrollee::dispatchNow($enrollee);
                $enrollee->fresh('user');
            }

            $enrollee->loadMissing('user.primaryPractice');

            if (is_null($enrollee->user)) {
                return;
            }

            SendInvitation::dispatch($enrollee->user, EnrollmentInvitationsBatch::manualInvitesBatch($enrollee->practice_id)->id);
        });

        Action::message('Invites should have been sent. Please check invitation panel.');
    }
}
