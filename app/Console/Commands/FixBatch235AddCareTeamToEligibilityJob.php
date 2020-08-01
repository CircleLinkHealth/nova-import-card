<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class FixBatch235AddCareTeamToEligibilityJob extends Command
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
    protected $description = 'Batch 235';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:care-team';

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
        EligibilityJob::whereBatchId(235)->with('targetPatient')->chunkById(100, function (Collection $eJs) {
            $eJs->each(function (EligibilityJob $eJ) {
                $careTeam = $this->athenaApiImplementation->getCareTeam($eJ->targetPatient->ehr_patient_id, $eJ->targetPatient->ehr_practice_id, $eJ->targetPatient->ehr_department_id);

                if (is_array($careTeam)) {
                    $data = $eJ->data;
                    $data['care_team'] = $careTeam;
                    $eJ->data = $data;
                    $eJ->save();
                }
            });
        });
    }
}
