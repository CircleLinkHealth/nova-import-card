<?php

namespace App\Jobs;
/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */


use App\Console\Commands\SendEnrollmentNotifications;
use Carbon\Carbon;
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
     * @var Role
     */
    private $surveyRole;
    private $enrollee;

    public function __construct(Enrollee $enrollee = null)
    {
        $this->enrollee = $enrollee;
    }

    /**
     * Execute the job.
     *
     * @param Enrollee|null $enrollee
     * @return void
     */
    public function handle()
    {
        if (!is_null($this->enrollee)) {
            return $this->createUserFromEnrolleeAndInvite($this->enrollee);
        }

        if (App::environment(['local', 'review'])) {
            $practiceId = Practice::whereName('demo')->firstOrFail()->id;
            $enrollees = $this->getEnrollees()
                ->where('practice_id', $practiceId)
                ->where('dob', Carbon::parse('1901-01-01'))
                ->get()
                ->take(SendEnrollmentNotifications::SEND_NOTIFICATIONS_LIMIT_FOR_TESTING)
                ->all();
            $this->createSurveyOnlyUserFromEnrollees($enrollees);
        } else {
            $this->getEnrollees()->chunk(50, function ($enrollees) {
                $this->createSurveyOnlyUserFromEnrollees($enrollees);
            });
        }
    }

    /**
     * @param Enrollee $enrollee
     */
    public function createUserFromEnrolleeAndInvite(Enrollee $enrollee)
    {
        $surveyRole = $this->surveyRole();
        CreateUserFromEnrollee::dispatch($enrollee, $surveyRole->id);
//            $this->sendSms($userCreatedFromEnrollee);
    }

    private function surveyRole(): Role
    {
        if (!$this->surveyRole) {
            $this->surveyRole = Role::firstOrCreate(
                [
                    'name' => 'survey-only'
                ],
                [
                    'display_name' => 'Survey User',
                    'description' => 'Became Users just to be enrolled in AWV survey'
                ]
            );
        }

        return $this->surveyRole;
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
    public function createSurveyOnlyUserFromEnrollees(iterable $enrollees)
    {
        foreach ($enrollees as $enrollee) {
            $this->createUserFromEnrolleeAndInvite($enrollee);
        }
    }

    private function sendSms(User $userCreatedFromEnrollee)
    {
//        $userCreatedFromEnrollee->notify(new SendEnrollementSms($userCreatedFromEnrollee));
    }
}
