<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\SelfEnrollment\Jobs;

use App\Helpers\SelfEnrollmentHelpers;
use App\SelfEnrollment\Domain\RemindEnrollees;
use App\SelfEnrollment\Domain\RemindUnreachablePatients;
use App\SelfEnrollment\Domain\UnreachablesFinalAction;
use CircleLinkHealth\Core\Entities\AppConfig;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class DispatchSelfEnrollmentDomainAction implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var string
     */
    private $action;

    /**
     * DispatchSelfEnrollmentDomainAction constructor.
     */
    public function __construct(string $action)
    {
        $this->action = $action;
    }

    /**
     * All actions that can be dispatched by this Dispatcher Class.
     *
     * Att these actions are related to patient-CPM interaction via SMS, and Email.
     * Future iterations may include adding IVR support.
     *
     * @return string[]
     */
    public static function actions()
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
        [
            $endDate,
            $startDate,
            $practiceId
        ] = $this->prepareArguments();

        with(new $this->action(
            $endDate,
            $startDate,
            $practiceId
        ))->run();
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

    private function prepareArguments()
    {
        if ( ! in_array($this->action, self::actions())) {
            throw new \Exception("`{$this->action}` is not a valid action.");
        }

        $testingMode = filter_var(AppConfig::pull('testing_enroll_sms', true), FILTER_VALIDATE_BOOLEAN) || App::environment('testing');

        if ($testingMode) {
            $practiceId = SelfEnrollmentHelpers::getDemoPractice()->id;
            $startDate  = now()->startOfDay();
            $endDate    = $startDate->copy()->endOfDay();
        } else {
            $practiceId = null;
            $startDate  = now()->copy()->subHours(48)->startOfDay();
            $endDate    = $startDate->copy()->endOfDay();
        }

        return [
            $endDate,
            $startDate,
            $practiceId,
        ];
    }
}
