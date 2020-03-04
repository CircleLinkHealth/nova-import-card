<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Traits\ValidatesDates;
use CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation;
use CircleLinkHealth\Eligibility\Entities\TargetPatient;
use CircleLinkHealth\Eligibility\Jobs\FetchEncountersFromAthena;
use Illuminate\Console\Command;
use Illuminate\Validation\Rule;

class FixAthenaBatchAddLastEncounter extends Command
{
    use ValidatesDates;
    /**
     * @var AthenaApiImplementation
     */
    protected $api;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch last encounter from Athena for each record in this batch.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batch:add-last-encounter {batch_id} {start_date?} {end_date?}';

    /**
     * Create a new command instance.
     */
    public function __construct(AthenaApiImplementation $api)
    {
        parent::__construct();
        $this->api = $api;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        ini_set('memory_limit', '2000M');

        $this->validateArguments();

        TargetPatient::whereBatchId($this->argument('batch_id'))->with(['eligibilityJob', 'batch'])->has(
            'eligibilityJob'
        )->chunk(
            500,
            function ($targetPatients) {
                $targetPatients->each(
                    function ($targetPatient) {
                        $this->warn('Processing targetPatient:'.$targetPatient->id);
                        $this->processEncounters($targetPatient);
                    }
                );
            }
        );

        $this->line('batch finished');
    }

    /**
     * @throws \Exception
     */
    private function processEncounters(TargetPatient $targetPatient)
    {
        FetchEncountersFromAthena::dispatch(
            $targetPatient,
            $this->hasArgument('start_date')
                ? $this->argument('start_date')
                : null,
            $this->hasArgument('end_date')
                ? $this->argument('end_date')
                : null
        );
    }

    private function validateArguments()
    {
        $validator = \Validator::make(
            [
                'batch_id'   => $this->argument('batch_id'),
                'start_date' => $this->hasArgument('start_date')
                    ? $this->argument('start_date')
                    : null,
                'end_date' => $this->hasArgument('end_date')
                    ? $this->argument('end_date')
                    : null,
            ],
            [
                'batch_id' => [
                    'required',
                    Rule::exists('eligibility_batches', 'id'),
                ],
                'start_date' => [
                    'date',
                    'nullable',
                ],
                'end_date' => [
                    'date',
                    'nullable',
                ],
            ]
        );

        if ($validator->fails()) {
            throw new \Exception(json_encode($validator->errors()->all()));
        }
    }
}
