<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Events\AutoEnrollableCollected;
use App\Http\Controllers\Enrollment\AutoEnrollmentCenterController;
use App\Notifications\SendEnrollmentEmail;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class SelfEnrollmentUnreachablePatients implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    /**
     * @var int
     */
    private $amount;
    /**
     * @var int
     */
    private $practiceId;

    /**
     * @var User|null
     */
    private $user;

    /**
     * Create a new job instance.
     */
    public function __construct(
        User $user = null,
        int $amount,
        int $practiceId
    ) {
        $this->user       = $user;
        $this->amount     = $amount;
        $this->practiceId = $practiceId;
    }

    /**
     * NOTE: "whereDoesntHave" makes sure we dont invite Unreachable/Non responded - Users (Patients) second time.
     *
     * @return void
     */
    public function handle()
    {
        if ( ! is_null($this->user)) {
//            $this->user->notify(new SendEnrollmentEmail());
            event(new AutoEnrollableCollected($this->user));

            return;
        }

//        $monthStart = Carbon::parse(now())->copy()->startOfMonth()->toDateTimeString();
//        $monthEnd   = Carbon::parse($monthStart)->copy()->endOfMonth()->toDateTimeString();

        //    Just for testing
        if (App::environment(['local', 'review', 'staging', 'production'])) {
            $practiceId = Practice::where('name', '=', 'demo')->firstOrFail()->id;
            $patients   = $this->getUnreachablePatients($practiceId)
                ->whereHas('patientInfo', function ($patientInfo) {
                    $patientInfo->where('birth_date', Carbon::parse('1901-01-01'));
                })
                ->get()
                ->take(AutoEnrollmentCenterController::SEND_NOTIFICATIONS_LIMIT_FOR_TESTING);
            foreach ($patients->all() as $patient) {
                /** @var User $patient */
                if ( ! $patient->checkForSurveyOnlyRole()) {
                    event(new AutoEnrollableCollected($patient));
                }
            }
        } else {
            $patients = $this->getUnreachablePatients($this->practiceId)
                ->orderBy('id', 'asc')
                ->limit($this->amount)
                ->get();

            foreach ($patients as $patient) {
                /** @var User $patient */
                if ( ! $patient->checkForSurveyOnlyRole()) {
                    event(new AutoEnrollableCollected($patient));
                }
            }
        }
    }

    /**
     * @param $practiceId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getUnreachablePatients($practiceId)
    {
        return User::with('patientInfo')
            ->whereDoesntHave('enrollmentInvitationLink')
            ->where('program_id', $practiceId)
            ->whereHas('patientInfo', function ($patient) {
                $patient->where('ccm_status', 'unreachable');
            });
    }
}
