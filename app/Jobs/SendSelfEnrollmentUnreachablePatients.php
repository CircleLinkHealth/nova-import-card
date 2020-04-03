<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Console\Commands\SendEnrollmentNotifications;
use App\Notifications\SendEnrollementSms;
use App\Notifications\SendEnrollmentEmail;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class SendSelfEnrollmentUnreachablePatients implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * NOTE: "whereDoesntHave" makes sure we dont invite Unreachable/Non responded - Users (Patients) second time.
     *
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $mothStart = Carbon::parse(now())->copy()->startOfMonth()->toDateTimeString();
        $monthEnd  = Carbon::parse($mothStart)->copy()->endOfMonth()->toDateTimeString();

        //    Just for testing
        if (App::environment(['local', 'review', 'staging'])) {
            $patients = $this->getUnreachablePatients($mothStart, $monthEnd)->get()->take(SendEnrollmentNotifications::SEND_NOTIFICATIONS_LIMIT_FOR_TESTING);
            foreach ($patients->all() as $patient) {
                /** @var User $patient */
                if ( ! $patient->checkForSurveyOnlyRole()) {
                    $patient->notify(new SendEnrollmentEmail($patient));
//                    $patient->notify(new SendEnrollementSms($patient));
                }
            }
        } else {
            $this->getUnreachablePatients($mothStart, $monthEnd)->chunk(50, function ($patients) {
                foreach ($patients as $patient) {
                    /** @var User $patient */
                    if ( ! $patient->checkForSurveyOnlyRole()) {
                        $patient->notify(new SendEnrollmentEmail($patient));
                        $patient->notify(new SendEnrollementSms($patient));
                    }
                }
            });
        }
    }

    /**
     * @param $mothStart
     * @param $monthEnd
     *
     * @return \Illuminate\Database\Eloquent\Builder|User
     */
    private function getUnreachablePatients($mothStart, $monthEnd)
    {
        return User::with('patientInfo')
            ->whereDoesntHave('enrollmentInvitationLink')
            ->whereHas('patientInfo', function ($patient) use ($mothStart, $monthEnd) {
                $patient->where('ccm_status', 'unreachable')->where([
                    ['date_unreachable', '>=', $mothStart],
                    ['date_unreachable', '<=', $monthEnd],
                ]);
            });
    }
}
