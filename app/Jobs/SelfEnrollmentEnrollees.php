<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

// This file is part of CarePlan Manager by CircleLink Health.

use App\Traits\EnrollableManagement;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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
        int $amount = 0,
        int $practiceId = 0
    ) {
        $this->enrollee   = $enrollee;
        $this->color      = $color;
        $this->amount     = $amount;
        $this->practiceId = $practiceId;
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
//        @todo: Move to its own class
        if ( ! is_null($this->enrollee)) {
//            return $this->createSurveyOnlyUsers([$this->enrollee->id]);
        }

        $this->getEnrollees($this->practiceId)
            ->orderBy('id', 'asc')
            ->limit($this->amount)
            ->select(['id'])
            ->get()
            //needs to go after the get(), because we are using `limit`. otherwise `chunk` would override `limit`
            ->chunk(100)
            ->each(function ($coll) {
                $arr = $coll
                    ->map(function ($item) {
                        return $item->id;
                    })
                    ->toArray();
//                $this->createSurveyOnlyUsers($arr);
            });
    }

//    private function createSurveyOnlyUsers(array $enrolleeIds)
//    {
//        $surveyRole = $this->surveyRole();
//        CreateUsersFromEnrollees::dispatch($enrolleeIds, $surveyRole->id, $this->color);
//    }
//
//    private function surveyRole(): Role
//    {
//        if ( ! $this->surveyRole) {
//            $this->surveyRole = Role::firstOrCreate(
//                [
//                    'name' => 'survey-only',
//                ],
//                [
//                    'display_name' => 'Survey User',
//                    'description'  => 'Became Users just to be enrolled in AWV survey',
//                ]
//            );
//        }
//
//        return $this->surveyRole;
//    }
}
