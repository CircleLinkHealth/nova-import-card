<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

// This file is part of CarePlan Manager by CircleLink Health.

use App\Http\Controllers\Enrollment\AutoEnrollmentCenterController;
use App\Traits\EnrollableManagement;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EnrollmentMassInviteEnrollees implements ShouldQueue
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
     * @var int|mixed
     */
    private $practiceId;

    /**
     * @var Role
     */
    private $surveyRole;
    
    /**
     * EnrollmentMassInviteEnrollees constructor.
     * @param int $amount
     * @param int $practiceId
     * @param string $color
     */
    public function __construct(
        int $amount,
        int $practiceId,
        string $color = AutoEnrollmentCenterController::DEFAULT_BUTTON_COLOR
    ) {
        $this->amount     = $amount;
        $this->practiceId = $practiceId;
        $this->color      = $color;
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

                $x = 1;
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
