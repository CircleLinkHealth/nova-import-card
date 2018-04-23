<?php

namespace App\Jobs;

use App\EligibilityBatch;
use App\Models\PatientData\PhoenixHeart\PhoenixHeartInsurance;
use App\Models\PatientData\PhoenixHeart\PhoenixHeartName;
use App\Models\PatientData\PhoenixHeart\PhoenixHeartProblem;
use App\Practice;
use App\Repositories\Cache\UserNotificationList;
use App\Services\Cache\NotificationService;
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
     * @return void
     * @throws \Exception
     */
    public function handle(NotificationService $notificationService)
    {
        $names = PhoenixHeartName::where('processed', '=', false)
                                 ->take(50)
                                 ->get()
                                 ->keyBy('patient_id');

        if ($names->isEmpty()) {
            $this->batch->status = EligibilityBatch::STATUSES['complete'];
            $this->batch->save();

            return true;
        } else {
            $this->batch->status = EligibilityBatch::STATUSES['processing'];
            $this->batch->save();
        }

        $patientList = $names->map(function ($patient) {
            //format problems list
            $problems = PhoenixHeartProblem::where('patient_id', '=', $patient->patient_id)->get();
            $patient  = collect($patient->toArray());
            $patient->put('problems', collect());

            foreach ($problems as $problem) {
                if (str_contains($problem->code, ['-'])) {
                    $pos         = strpos($problem->code, '-') + 1;
                    $problemCode = mb_substr($problem->code, $pos);
                } elseif (str_contains($problem->code, ['ICD'])) {
                    $pos         = strpos($problem, 'ICD') + 3;
                    $problemCode = mb_substr($problem->code, $pos);
                } else {
                    $problemCode = $problem->code;
                }

                if ( ! $problemCode) {
                    continue;
                }

                $patient['problems']->push($problemCode);
            }

            //format insurances
            $insurances = PhoenixHeartInsurance::where('patient_id', '=', $patient->get('patient_id'))
                                               ->get()
                                               ->sortBy('order');

            $patient->put('primary_insurance', $insurances->get(0)->name ?? null);
            $patient->put('secondary_insurance', $insurances->get(1)->name ?? null);

            return $patient;
        })->map(function ($p) {
            $list = (new WelcomeCallListGenerator(collect([0 => $p]), false, true, true, true,
                Practice::whereName('phoenix-heart')->firstOrFail(), null, null, $this->batch));

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
}
