<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class FixBatch235AddProviderToEnrollee extends Command
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
    protected $description = 'Command description';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fixbatch235:add-provider-to-enrollees';

    /**
     * @var int
     */
    private $counter = 0;

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
        $this->counter = 0;

        Enrollee::whereBatchId(235)->with('eligibilityJob.targetPatient')->chunkById(200, function (Collection $enrollees) {
            $enrollees->each(function (Enrollee $enrollee) {
                $this->warn("Processing enrollee:$enrollee->id");

//                $previous = $enrollee->revisionHistory()->where('key', 'referring_provider_name')->first()->old_value;
//
//                $enrollee->referring_provider_name = $previous;
//                $enrollee->save();

                $eJ = $enrollee->eligibilityJob;

//                if ( ! array_key_exists('care_team', $eJ->data)) {
//                    $careTeam = $this->athenaApiImplementation->getCareTeam($eJ->targetPatient->ehr_patient_id, $eJ->targetPatient->ehr_practice_id, $eJ->targetPatient->ehr_department_id);
//
//                    if (is_array($careTeam)) {
//                        $data = $eJ->data;
//                        $data['care_team'] = $careTeam;
//                        $eJ->data = $data;
//                        $eJ->save();
//                    }
//                }

                try {
                    if ( ! array_key_exists('patient_demographics', $eJ->data)) {
                        $demographics = $this->athenaApiImplementation->getDemographics($eJ->targetPatient->ehr_patient_id, $eJ->targetPatient->ehr_practice_id);

                        if (is_array($demographics)) {
                            $data = $eJ->data;
                            $data['patient_demographics'] = $demographics;
                            $eJ->data = $data;
                            $eJ->save();
                        }
                    }

                    if ($provId = $eJ->data['patient_demographics'][0]['primaryproviderid']) {
                        $provider = $this->athenaApiImplementation->getProvider($eJ->targetPatient->ehr_practice_id, $provId);
                    }
                } catch (\Exception $e) {
                    $this->error($e->getMessage().PHP_EOL.$e->getFile().PHP_EOL.$e->getLine());

                    return;
                }

//
//
//                $provider = collect($eJ->data['care_team']['members'] ?? [])->where('recipientclass.description', 'Pain Management')->first();
//
//                $enrollee->referring_provider_name = $provider['name'];
                $enrollee->referring_provider_name = $provider[0]['displayname'];
                $enrollee->save();

                $this->line("Finished enrollee:$enrollee->id");
                ++$this->counter;
            });
        });
        $this->info("Total Enrolees Processed: $this->counter");
    }
}
