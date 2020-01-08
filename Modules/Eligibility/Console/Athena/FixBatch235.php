<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Console\Athena;

use App\Models\MedicalRecords\Ccda;
use Carbon\Carbon;
use CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class FixBatch235 extends Command
{
    /**
     * @var AthenaApiImplementation
     */
    protected $athenaApiImplementation;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make some changes to batch with id 235';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:batch235';

    /**
     * Create a new command instance.
     */
    public function __construct(AthenaApiImplementation $athenaApiImplementation)
    {
        parent::__construct();
        $this->athenaApiImplementation = $athenaApiImplementation;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Ccda::with('targetPatient.eligibilityJob.enrollee')->has('targetPatient')->whereBatchId(235)->chunkById(100, function (Collection $ccdas) {
            $ccdas->each(function (Ccda $ccd) {
                $this->warn("Starting CCD $ccd->id");
                $ccd->json = null;

                $json = $ccd->bluebuttonJson();

                $eligibilityJob = $ccd->targetPatient->eligibilityJob;

                if ( ! $eligibilityJob) {
                    return;
                }

                $enrollee = $eligibilityJob->enrollee;

                if ( ! $enrollee) {
                    return;
                }

                $encounters = collect($json->encounters);

                $lastEncounter = $encounters->sortByDesc(function ($el) {
                    return $el->date;
                })->first();

                if (property_exists($lastEncounter, 'date')) {
                    $v = \Validator::make(['date' => $lastEncounter->date], ['date' => 'required|date']);

                    if ($v->passes()) {
                        $enrollee->last_encounter = Carbon::parse($lastEncounter->date);
                        $enrollee->save();
                    }
                }

                $careTeam = $this->athenaApiImplementation->getCareTeam($ccd->targetPatient->ehr_patient_id, $ccd->targetPatient->ehr_practice_id, $ccd->targetPatient->ehr_department_id);

                if (is_array($careTeam)) {
                    foreach ($careTeam['members'] as $member) {
                        if (array_key_exists('firstname', $member)) {
                            $providerName = $member['name'];

                            $enrollee->referring_provider_name = $ccd->referring_provider_name = $providerName;
                            $enrollee->save();
                            $ccd->save();

                            $data = $eligibilityJob->data;
                            $data['referring_provider_name'] = $providerName;
                            $eligibilityJob->save();

                            break;
                        }
                    }
                }

                $this->line("Finished CCD $ccd->id!");
            });
        });
    }
}
