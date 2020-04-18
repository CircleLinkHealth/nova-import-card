<?php

namespace App\Jobs;
/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */


use App\Console\Commands\SendEnrollmentNotifications;
use App\Notifications\SendEnrollementSms;
use App\Notifications\SendEnrollmentEmail;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class SendSelfEnrollmentEnrollees implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    const SURVEY_ONLY = 'survey-only';

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (App::environment(['local', 'staging', 'review'])) {
            $practiceId = Practice::whereName('demo')->firstOrFail()->id;
            $enrollees = $this->getEnrollees()
                ->where('practice_id', $practiceId)
                ->where('dob', Carbon::parse('1901-01-01'))
                ->get()
                ->take(SendEnrollmentNotifications::SEND_NOTIFICATIONS_LIMIT_FOR_TESTING)
                ->all();
            $this->createSurveyOnlyUserFromEnrollee($enrollees);
        } else {
            $this->getEnrollees()->chunk(50, function ($enrollees) {
                $this->createSurveyOnlyUserFromEnrollee($enrollees);
            });
        }
    }

    /**
     * NOTE: "whereDoesntHave" makes sure we dont invite Unreachable/Non responded - Enrollees second time.
     *
     * @return Enrollee|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    private function getEnrollees()
    {
        return Enrollee::whereDoesntHave('enrollmentInvitationLink')
            ->whereIn('status', [
                'call_queue',
                'utc',
            ]);
    }

    /**
     * First we create temporary user from Enrollee.
     *
     * @param $enrollees
     */
    public function createSurveyOnlyUserFromEnrollee($enrollees)
    {
        foreach ($enrollees as $enrollee) {

            $surveyRole = Role::firstOrCreate(
                [
                    'name' => 'survey-only'
                ],
                [
                    'display_name' => 'Survey User',
                    'description' => 'Became Users just to be enrolled in AWV survey'
                ]
            );

            $surveyRoleId = $surveyRole->id;
            //                Create User model from enrollee
            /** @var User $userCreatedFromEnrollee */
            /** @var Enrollee $enrollee */
            $userCreatedFromEnrollee = $enrollee->user()->updateOrCreate(
                [
                    'email' => $enrollee->email,
                ],
                [
                    'program_id' => $enrollee->practice_id,
                    'display_name' => $enrollee->first_name . ' ' . $enrollee->last_name,
                    'user_registered' => Carbon::parse(now())->toDateTimeString(),
                    'first_name' => $enrollee->first_name,
                    'last_name' => $enrollee->last_name,
                    'address' => $enrollee->address,
                    'address_2' => $enrollee->address_2,
                    'city' => $enrollee->city,
                    'state' => $enrollee->state,
                    'zip' => $enrollee->zip,
                ]
            );

            $userCreatedFromEnrollee->attachGlobalRole($surveyRoleId);

            $userCreatedFromEnrollee->phoneNumbers()->create([
                'number' => $enrollee->primary_phone,
                'is_primary' => true,
            ]);

            $userCreatedFromEnrollee->patientInfo()->create([
                'birth_date' => $enrollee->dob,
            ]);

            // Why this does not work in create query above?
            $userCreatedFromEnrollee->patientInfo->update([
                'ccm_status' => Patient::UNREACHABLE,
            ]);

            $userCreatedFromEnrollee->setBillingProviderId($enrollee->provider->id);

            $enrollee->update(['user_id' => $userCreatedFromEnrollee->id]);
            $this->sendEmail($userCreatedFromEnrollee);
            $this->sendSms($userCreatedFromEnrollee);
        }
    }

    private function sendEmail(User $userCreatedFromEnrollee)
    {
        $userCreatedFromEnrollee->notify(new SendEnrollmentEmail($userCreatedFromEnrollee));
    }

    private function sendSms(User $userCreatedFromEnrollee)
    {
//        $userCreatedFromEnrollee->notify(new SendEnrollementSms($userCreatedFromEnrollee));
    }
}
