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
use App\Practice;
use App\Repositories\Cache\UserNotificationList;
use App\Services\Eligibility\Entities\Problem;
use App\Services\WelcomeCallListGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MakePhoenixHeartWelcomeCallList implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var EligibilityBatch
     */
    private $batch;

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
        $names = PhoenixHeartName::where('processed', '=', false)
            ->take(30)
            ->get()
            ->keyBy('patient_id');

        $phxPractice = Practice::whereName('phoenix-heart')->firstOrFail();

        $patientList = $names->map(function ($patient) {
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

                $patient['problems']->push(Problem::create([
                    'name'  => $problem->description,
                    'code'  => $problemCode,
                    'start' => $problem->start_date,
                    'end'   => $problem->end_date,
                ]));
            }

            //format insurances
            $insurances = PhoenixHeartInsurance::where('patient_id', '=', $patient->get('patient_id'))
                ->get()
                ->transform(function ($i) {
                    $i->name = trim($i->name);

                    return $i;
                })
                ->unique('name')
                ->sortBy('order')
                ->pluck('name')
                ->map(function ($ins) {
                    if ( ! $ins) {
                        return null;
                    }

                    return ['type' => $ins];
                })
                ->filter()
                ->values();

            $patient->put('insurances', $insurances);

            return $patient;
        })->map(function ($p) use ($phxPractice) {
            $job = $this->createEligibilityJob($p, $phxPractice);

            $list = (new WelcomeCallListGenerator(
                collect([0 => $p]),
                false,
                true,
                true,
                true,
                $phxPractice,
                null,
                null,
                $this->batch,
                $job
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
        });
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

        return EligibilityJob::create([
            'batch_id' => $this->batch->id,
            'hash'     => $hash,
            'data'     => $p,
        ]);
    }
}
