<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Console;

use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use CircleLinkHealth\Eligibility\Entities\TargetPatient;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ResetAthenaEligibilityBatch extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set all Medical Records for an Athena.';
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'batch-reset:athena';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->warn('Resetting batch:'.$this->argument('batch_id'));

        TargetPatient::whereBatchId($this->argument('batch_id'))->update(
            [
                'status' => TargetPatient::STATUS_TO_PROCESS,
            ]
        );

        $this->line('Finished resetting target patients.');

        EligibilityJob::whereBatchId($this->argument('batch_id'))->update(
            [
                'status'              => 0,
                'outcome'             => null,
                'reason'              => null,
                'messages'            => null,
                'errors'              => null,
                'bhi_problem_id'      => null,
                'ccm_problem_2_id'    => null,
                'ccm_problem_1_id'    => null,
                'tertiary_insurance'  => null,
                'secondary_insurance' => null,
                'primary_insurance'   => null,
                'last_encounter'      => null,
                'invalid_data'        => null,
                'invalid_structure'   => null,
                'invalid_mrn'         => null,
                'invalid_first_name'  => null,
                'invalid_last_name'   => null,
                'invalid_dob'         => null,
                'invalid_problems'    => null,
                'invalid_phones'      => null,
            ]
        );

        $this->line('Finished resetting eligibility jobs.');

        Enrollee::whereBatchId($this->argument('batch_id'))->delete();

        $this->line('Finished deleting enrollees.');

        EligibilityBatch::whereId($this->argument('batch_id'))->update(
            [
                'status' => 0,
            ]
        );

        $this->line('Finished resetting eligibility batch.');

        $this->line('Job Finished!');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['batch_id', InputArgument::REQUIRED, 'The ID of the batch to reset.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
        ];
    }
}
