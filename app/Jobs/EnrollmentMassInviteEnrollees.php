<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

// This file is part of CarePlan Manager by CircleLink Health.

use App\Events\AutoEnrollableCollected;
use App\Http\Controllers\Enrollment\AutoEnrollmentCenterController;
use App\Traits\EnrollableManagement;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

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
            ->select(['user_id'])
            ->get()
            //needs to go after the get(), because we are using `limit`. otherwise `chunk` would override `limit`
            ->chunk(100)
            ->each(function ($coll) {
                $arr = $coll
                    ->map(function ($item) {
                        return $item->user_id;
                    })
                    ->toArray();

                if (empty($arr)) {
                    Log::warning('Enrollees to invite has not been found');
                }
                AutoEnrollableCollected::dispatch($arr, false, $this->color);
            });
    }
}
