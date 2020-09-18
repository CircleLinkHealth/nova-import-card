<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Console\Athena;

use Carbon\Carbon;
use CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use CircleLinkHealth\SharedModels\Entities\Ccda;
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
        Enrollee::where('referring_provider_name', '')->where('batch_id', 235)->with('eligibilityJob.targetPatient.ccda')->chunkById(100, function (Collection $enrollees) {
            $enrollees->each(function (Enrollee $enrollee) {
                $eligibilityJob = $enrollee->eligibilityJob;
                /** @var Ccda $ccd */
                $ccd = $eligibilityJob->targetPatient->ccda;
                $this->warn("Starting CCD $ccd->id");
                $ccd->json = null;

                $json = $ccd->bluebuttonJson();

                $encounters = collect($json->encounters);

                $lastEncounter = $encounters->sortByDesc(function ($el) {
                    return $el->date;
                })->first();

                if (property_exists($lastEncounter, 'date')) {
                    $v = \Validator::make(['date' => $lastEncounter->date], ['date' => 'required|date']);

                    if ($v->passes()) {
                        $lastEncounterCarbon = Carbon::parse($lastEncounter->date);
                        $enrollee->last_encounter = $lastEncounterCarbon;
                        $enrollee->save();

                        $data = $eligibilityJob->data;
                        $data['last_encounter'] = $lastEncounterCarbon->toDateTimeString();
                        $eligibilityJob->last_encounter = $lastEncounterCarbon;
                        $eligibilityJob->data = $data;
                        $eligibilityJob->save();
                    }
                }

                $careTeam = $this->athenaApiImplementation->getCareTeam($ccd->targetPatient->ehr_patient_id, $ccd->targetPatient->ehr_practice_id, $ccd->targetPatient->ehr_department_id);

                if (is_array($careTeam)) {
                    foreach ($careTeam['members'] as $member) {
                        if (array_key_exists('name', $member)) {
                            $providerName = $member['name'];

                            $enrollee->referring_provider_name = $ccd->referring_provider_name = $providerName;
                            $enrollee->save();
                            $ccd->save();

                            $data = $eligibilityJob->data;
                            $data['referring_provider_name'] = $providerName;
                            $eligibilityJob->data = $data;
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
