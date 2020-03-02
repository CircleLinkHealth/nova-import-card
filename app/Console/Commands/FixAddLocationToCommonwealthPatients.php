<?php

namespace App\Console\Commands;

use CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation;
use CircleLinkHealth\Eligibility\Decorators\DepartmentFromAthena;
use CircleLinkHealth\Eligibility\Entities\TargetPatient;
use Illuminate\Console\Command;

class FixAddLocationToCommonwealthPatients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batch:add-department {batchId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add department (location) from Athena API to each patient in the given batch.';
    /**
     * @var AthenaApiImplementation
     */
    protected $api;
    
    /**
     * Create a new command instance.
     *
     * @param AthenaApiImplementation $api
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
    
        TargetPatient::whereBatchId($this->argument('batchId'))->with(['eligibilityJob', 'batch'])->has(
            'eligibilityJob'
        )->chunk(
            500,
            function ($targetPatients) {
                $targetPatients->each(
                    function ($targetPatient) {
                        $this->warn("processing targetPatient:$targetPatient->id");
                        $eligibilityJob = app(DepartmentFromAthena::class)->decorate(
                            $targetPatient->eligibilityJob
                        );
                    }
                );
            }
        );
        
        $this->line('Command done');
    }
}
