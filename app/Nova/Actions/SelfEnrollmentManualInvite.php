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

            $manualInviteBatch = now()->format(EnrollmentInvitationsBatch::TYPE_FIELD_DATE_HUMAN_FORMAT).':'.EnrollmentInvitationsBatch::MANUAL_INVITES_BATCH_TYPE;
            SendInvitation::dispatch($enrollee->user, EnrollmentInvitationsBatch::firstOrCreateAndRemember(
                $enrollee->practice_id,
                $manualInviteBatch
            )->id);
        });

        Action::message('Invites should have been sent. Please check invitation panel.');
    }
}
