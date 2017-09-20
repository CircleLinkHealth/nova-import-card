<?php

namespace App\Jobs;

use App\Models\PatientData\PhoenixHeart\PhoenixHeartInsurance;
use App\Models\PatientData\PhoenixHeart\PhoenixHeartName;
use App\Models\PatientData\PhoenixHeart\PhoenixHeartProblem;
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
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $names = PhoenixHeartName::where('processed', '=', false)
            ->get()
            ->keyBy('patient_id');

        $patientList = $names->map(function ($patient) {
            //format problems list
            $problems = PhoenixHeartProblem::where('patient_id', '=', $patient->patient_id)->get();
            $patient = collect($patient->toArray());
            $patient->put('problems', collect());

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

                if (!$problemCode) {
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

            PhoenixHeartName::where('patient_id', '=', $patient['patient_id'])
                ->update([
                    'processed' => true,
                ]);

            return $patient;
        });

        $list = (new WelcomeCallListGenerator($patientList, false, true, true, false));

        $list->exportToCsv(false, true, 'Phoenix Heart');
    }
}
