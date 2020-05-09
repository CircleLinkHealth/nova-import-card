<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class UpdateEnrolleeSurveyStatuses implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    public $status;

    public $userId;

    /**
     * Create a new job instance.
     *
     * @param $userId
     * @param $status
     */
    public function __construct($userId, $status)
    {
        $this->userId = $userId;
        $this->status = $status;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::table('self_enrollment_statuses')->where('enrollee_user_id', $this->userId)->update([
            'awv_survey_status' => $this->status,
        ]);
    }
}
