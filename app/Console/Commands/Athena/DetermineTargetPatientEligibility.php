<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands\Athena;

use App\Services\AthenaAPI\DetermineEnrollmentEligibility;
use App\TargetPatient;
use Illuminate\Console\Command;

class DetermineTargetPatientEligibility extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieves problems and insurances of a given patient from the Athena API';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'athena:DetermineTargetPatientEligibility {batchId? : The Eligibility Batch Id}';

    private $service;

    /**
     * Create a new command instance.
     *
     * @param DetermineEnrollmentEligibility $athenaApi
     */
    public function __construct(DetermineEnrollmentEligibility $athenaApi)
    {
        parent::__construct();

        $this->service = $athenaApi;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        TargetPatient::where('status', '=', 'to_process')
            ->with('batch')
            ->get()
            ->each(function ($patient) {
                $this->service->determineEnrollmentEligibility($patient);
            });
    }
}
