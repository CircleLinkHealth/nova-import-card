<?php

namespace App\Jobs;

use App\CLH\Repositories\CCDImporterRepository;
use App\Importer\Loggers\Ccda\CcdToLogTranformer;
use App\Models\CCD\Problem;
use App\Models\MedicalRecords\Ccda;
use App\Models\ProblemCode;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReImportCcdToGetProblemTranslationCodes implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $patient;
    private $transformer;
    private $repo;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $patient)
    {
        $this->patient = $patient;
        $this->repo = new CCDImporterRepository();
        $this->transformer = new CcdToLogTranformer();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $ccda = Ccda::select(['id', 'patient_id', 'xml'])
            ->where('patient_id', '=', $this->patient->id)
            ->first();

        if (!$ccda) {
            return;
        }

        $parsed = json_decode($this->repo->toJson($ccda->xml));

        $ccdProblems = Problem::where('patient_id', '=', $this->patient->id)->get();

        $problems = collect($parsed->problems)->map(function ($prob) use ($ccdProblems) {
            $cons = $this->consolidateProblemInfo($prob);

            $ccdProblem = $ccdProblems->where('name', '=', $cons->cons_name)
                ->first();

            foreach ($prob->translations as $translation) {
                if (!$translation->code_system_name) {
                    $translation->code_system_name = getProblemCodeSystemName($translation);

                    if (!$translation->code_system_name) {
                        continue;
                    }
                }

                ProblemCode::updateOrCreate([
                    'problem_id'       => $ccdProblem->id,
                    'code_system_name' => $translation->code_system_name,
                    'code_system_oid'  => $translation->code_system,
                    'code'             => $translation->code,
                ]);
            }
        });
    }

    public function consolidateProblemInfo($problemLog)
    {
        $consolidatedProblem = new \stdClass();

        $consolidatedProblem->cons_name = $problemLog->name;

        if (empty($consolidatedProblem->cons_name) && !empty($problemLog->reference_title)) {
            $consolidatedProblem->cons_name = $problemLog->reference_title;
        }

        return $consolidatedProblem;
    }
}
