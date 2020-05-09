<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

// This file is part of CarePlan Manager by CircleLink Health.

use App\Traits\EnrollableManagement;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class SelfEnrollmentEnrollees implements ShouldQueue
{
    use Dispatchable;
    use EnrollableManagement;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    const SURVEY_ONLY = 'survey-only';
    /**
     * @var int
     */
    private $amount;
    /**
     * @var null
     */
    private $color;
    /**
     * @var Enrollee|null
     */
    private $enrollee;
    /**
     * @var int|mixed
     */
    private $practiceId;

    /**
     * @var Role
     */
    private $surveyRole;

    /**
     * SelfEnrollmentEnrollees constructor.
     *
     * @param null  $color
     * @param mixed $practiceId
     */
    public function __construct(
        Enrollee $enrollee = null,
        $color = null,
        int $amount,
        int $practiceId
    ) {
        $this->enrollee   = $enrollee;
        $this->color      = $color;
        $this->amount     = $amount;
        $this->practiceId = $practiceId;
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

    public function createUserFromEnrolleeAndInvite(Enrollee $enrollee)
    {
        $surveyRole = $this->surveyRole();
        CreateUserFromEnrollee::dispatch($enrollee, $surveyRole->id, $this->color);
    }

    /**
     * Execute the job.
     *
     * @param Enrollee|null $enrollee
     *
     * @return void
     */
    public function handle()
    {
        if ( ! is_null($this->enrollee)) {
            return $this->createUserFromEnrolleeAndInvite($this->enrollee);
        }

        if (App::environment(['testing'])) {
            $practice  = $this->getDemoPractice();
            $enrollees = $this->getEnrollees($practice->id)
                ->where('dob', Carbon::parse('1901-01-01'))
                ->get()
                ->take($this->amount)
                ->all();
            $this->createSurveyOnlyUserFromEnrollees($enrollees);
        } else {
            $enrollees = $this->getEnrollees($this->practiceId)
                ->orderBy('id', 'asc')
                ->limit($this->amount)
                ->get();
            $this->createSurveyOnlyUserFromEnrollees($enrollees);
        }
    }

    private function surveyRole(): Role
    {
        if ( ! $this->surveyRole) {
            $this->surveyRole = Role::firstOrCreate(
                [
                    'name' => 'survey-only',
                ],
                [
                    'display_name' => 'Survey User',
                    'description'  => 'Became Users just to be enrolled in AWV survey',
                ]
            );
        }

        return $this->surveyRole;
    }
}
