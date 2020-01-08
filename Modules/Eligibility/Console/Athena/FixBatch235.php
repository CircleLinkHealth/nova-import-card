<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Console\Athena;

use App\TargetPatient;
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
        TargetPatient::with(['eligibilityJob.enrollee', 'ccda'])->whereBatchId(235)->chunkById(100, function (Collection $tPs) {
            $tPs->each(function (TargetPatient $tp) {
                $this->warn("Starting CCD $tp->id");
                $tp->json = null;

                $json = $tp->bluebuttonJson();

                $targetPatient = $tp->targetPatient;

                if ( ! $targetPatient) {
                    $this->error("No target patient for CCD $tp->id");

                    return;
                }

                $eligibilityJob = $targetPatient->eligibilityJob;

                if ( ! $eligibilityJob) {
                    $this->error("No eligibility job for CCD $tp->id");

                    return;
                }

                $enrollee = $eligibilityJob->enrollee;

                if ( ! $enrollee) {
                    $this->error("No enrollee for CCD $tp->id");

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

                $careTeam = $this->athenaApiImplementation->getCareTeam($tp->targetPatient->ehr_patient_id, $tp->targetPatient->ehr_practice_id, $tp->targetPatient->ehr_department_id);

                if (is_array($careTeam)) {
                    foreach ($careTeam['members'] as $member) {
                        if (array_key_exists('firstname', $member)) {
                            $providerName = $member['name'];

                            $enrollee->referring_provider_name = $tp->referring_provider_name = $providerName;
                            $enrollee->save();
                            $tp->save();

                            $data = $eligibilityJob->data;
                            $data['referring_provider_name'] = $providerName;
                            $eligibilityJob->save();

                            break;
                        }
                    }
                }

                $this->line("Finished CCD $tp->id!");
            });
        });
    }
}
