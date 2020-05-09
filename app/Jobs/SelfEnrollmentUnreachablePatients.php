<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Events\AutoEnrollableCollected;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
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
            event(new AutoEnrollableCollected($this->user));

            return;
        }

//        //    Just for testing
//        if (App::environment(['testing'])) {
//            $practiceId = Practice::where('name', '=', 'demo')->firstOrFail()->id;
//            $patients   = $this->getUnreachablePatients($practiceId)
//                ->whereHas('patientInfo', function ($patientInfo) {
//                    $patientInfo->where('birth_date', Carbon::parse('1901-01-01'));
//                })
//                ->get()
//                ->take($this->amount);
//            foreach ($patients->all() as $patient) {
//                /** @var User $patient */
//                if ( ! $patient->checkForSurveyOnlyRole()) {
//                    event(new AutoEnrollableCollected($patient));
//                }
//            }
//        } else {
        $enrollableUnreachablePatient = array_slice($this->getUnreachablePatients($this->practiceId), 0, $this->amount);

        if ( ! empty($enrollableUnreachablePatient)) {
            event(new AutoEnrollableCollected($enrollableUnreachablePatient));
        }
    }

//    }

    /**
     * @param $practiceId
     * @param $amount
     * @return array
     */
    private function getUnreachablePatients($practiceId)
    {
        $userIds = [];
        Enrollee::whereNotNull('user_id')
            ->where('source', '=', Enrollee::UNREACHABLE_PATIENT)
            ->where('practice_id', $practiceId)
            ->whereDoesntHave('enrollmentInvitationLink')
            ->whereIn('status', [
                Enrollee::QUEUE_AUTO_ENROLLMENT,
            ])
            ->orderBy('id', 'asc')
            ->select('user_id')
            ->each(function ($unreachable) use (&$userIds) {
                $userIds[] = $unreachable->user_id;
            });

        return $userIds;
    }
}
