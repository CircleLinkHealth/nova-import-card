<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\SelfEnrollment\Jobs;

use App\Helpers\SelfEnrollmentHelpers;
use App\SelfEnrollment\Actions\RemindEnrollees;
use App\SelfEnrollment\Actions\RemindUnreachablePatients;
use App\SelfEnrollment\Actions\UnreachablesFinalAction;
use App\Traits\EnrollmentReminderShared;
use CircleLinkHealth\Core\Entities\AppConfig;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class DispatchSelfEnrollmentAction implements ShouldQueue
{
    use Dispatchable;
    use EnrollmentReminderShared;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var string
     */
    private $action;

    /**
     * DispatchSelfEnrollmentAction constructor.
     */
    public function __construct(string $action)
    {
        $this->action = $action;
    }

    public function actions()
    {
        return [
            RemindEnrollees::class,
            RemindUnreachablePatients::class,
            UnreachablesFinalAction::class,
        ];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ( ! in_array($this->action, $this->actions())) {
            throw new \Exception("`{$this->action}` is not a valid action.");
        }

        $testingMode = filter_var(AppConfig::pull('testing_enroll_sms', true), FILTER_VALIDATE_BOOLEAN) || App::environment('testing');

        if ($testingMode) {
            $practiceId    = SelfEnrollmentHelpers::getDemoPractice()->id;
            $twoDaysAgo    = now()->startOfDay();
            $untilEndOfDay = $twoDaysAgo->copy()->endOfDay();
        } else {
            $practiceId    = null;
            $twoDaysAgo    = now()->copy()->subHours(48)->startOfDay();
            $untilEndOfDay = $twoDaysAgo->copy()->endOfDay();
        }

        with(new $this->action($untilEndOfDay, $twoDaysAgo, $practiceId))->run();
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        return [
            'SelfEnrollment',
            $this->action,
        ];
    }
}
