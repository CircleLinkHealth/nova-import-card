<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\EligibilityBatch;
use App\EligibilityJob;
use App\Models\PatientData\PhoenixHeart\PhoenixHeartInsurance;
use App\Models\PatientData\PhoenixHeart\PhoenixHeartName;
use App\Models\PatientData\PhoenixHeart\PhoenixHeartProblem;
use App\Repositories\Cache\UserNotificationList;
use App\Services\Eligibility\Entities\Problem;
use App\Services\EligibilityChecker;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MakePhoenixHeartWelcomeCallList implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    const MAX_TIME_TO_PROCESS_BATCH_IN_HOURS = 6;

    /**
     * @var EligibilityBatch
     */
    private $batch;

    /**
     * @var Carbon
     */
    private $cutoffTime;

    public function __construct(EligibilityBatch $batch)
    {
        $this->batch = $batch;
    }

    /**
     * Execute the job.
     *
     * @param UserNotificationList $userNotificationListService
     *
     * @throws \Exception
     */
    public function handle()
    {
        $this->cutoffTime = Carbon::now()->subHours(self::MAX_TIME_TO_PROCESS_BATCH_IN_HOURS);

        $names = PhoenixHeartName::where('processed', '=', false)
            ->take(30)
            ->get()
            ->keyBy('patient_id');

        if ($names->isEmpty() && $this->batchRunningOverMaxProcessingTime()) {
            $this->deleteJobsInProgressOrNotStarted();
        }

        $phxPractice = Practice::whereName('phoenix-heart')->firstOrFail();

        $patientList = $names->map(
            function ($patient) {
                //format problems list
                $problems = PhoenixHeartProblem::where('patient_id', '=', $patient->patient_id)->get();
                $patient = collect($patient->toArray());
                $patient->put('problems', collect());
                $patient->put('street', $patient['address_1']);
                $patient->put('street2', $patient['address_2']);
                $patient->put('mrn', $patient['patient_id']);

                $patient->put('dob', $patient['dob']);
                $patient->put('first_name', $patient['patient_first_name']);
                $patient->put('last_name', $patient['patient_last_name']);
                $patient->put('city', $patient['city']);
                $patient->put('state', $patient['state']);
                $patient->put('zip', $patient['zip']);
                $patient->put('primary_phone', $patient['phone_1']);
                $patient->put('cell_phone', $patient['phone_2']);
                $patient->put('home_phone', $patient['phone_3']);

                $patient->put(
                    'referring_provider_name',
                    $patient['provider_last_name'].' '.$patient['provider_first_name']
                );

                foreach ($problems as $problem) {
                    if (str_contains($problem->code, ['-'])) {
                        $pos = strpos($problem->code, '-') + 1;
                        $problemCode = mb_substr($problem->code, $pos);
                    } elseif (str_contains($problem->code, ['ICD'])) {
                        $pos = strpos($problem, 'ICD') + 3;
                        $problemCode = mb_substr($problem->code, $pos);
                    } else {
                        $problemCode = $problem->code;
                    }

                    if ( ! $problemCode && ! $problem->description) {
                        continue;
                    }

                    $patient['problems']->push(
                        Problem::create(
                            [
                                'name'  => $problem->description,
                                'code'  => $problemCode,
                                'start' => $problem->start_date,
                                'end'   => $problem->end_date,
                            ]
                        )
                    );
                }

                //format insurances
                $insurances = PhoenixHeartInsurance::where('patient_id', '=', $patient->get('patient_id'))
                    ->get()
                    ->transform(
                        function ($i) {
                            $i->name = trim($i->name);

                            return $i;
                        }
                                                   )
                    ->unique('name')
                    ->sortBy('order')
                    ->pluck('name')
                    ->map(
                        function ($ins) {
                            if ( ! $ins) {
                                return null;
                            }

                            return ['type' => $ins];
                        }
                                                   )
                    ->filter()
                    ->values();

                $patient->put('insurances', $insurances);

                return $patient;
            }
        )->map(
            function ($p) use ($phxPractice) {
                $job = $this->createEligibilityJob($p, $phxPractice);

                $list = (new EligibilityChecker(
                    $job,
                    $phxPractice,
                    $this->batch,
                    false,
                    true,
                    true,
                    true
                ));

                if ($list->patientList->count() > 0) {
                    $attr = [
                        'processed' => true,
                        'eligible'  => true,
                    ];
                } else {
                    $attr = [
                        'processed' => true,
                        'eligible'  => false,
                    ];
                }

                return PhoenixHeartName::where('patient_id', '=', $p['patient_id'])
                    ->update($attr);
            }
        );
    }

    private function batchRunningOverMaxProcessingTime()
    {
        return $this->batch->created_at->lt($this->getCutoffTime());
    }

    /**
     * @param $p
     * @param $phxPractice
     *
     * @return EligibilityJob|\Illuminate\Database\Eloquent\Model
     */
    private function createEligibilityJob($p, $phxPractice)
    {
        $hash = $phxPractice->name.$p['first_name'].$p['last_name'].$p['mrn'].$p['city'].$p['state'].$p['zip'];

        return EligibilityJob::create(
            [
                'batch_id' => $this->batch->id,
                'hash'     => $hash,
                'data'     => $p,
            ]
        );
    }

    private function deleteJobsInProgressOrNotStarted()
    {
        return $this->batch->eligibilityJobs()
            ->where('status', '<', 2)
            ->where('created_at', '<', $this->getCutoffTime())
            ->delete();
    }

    private function getCutoffTime(): Carbon
    {
        return $this->cutoffTime;
    }
}
