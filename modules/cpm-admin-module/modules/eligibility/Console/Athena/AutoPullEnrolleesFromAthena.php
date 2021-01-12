<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Console\Athena;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;
use CircleLinkHealth\Eligibility\ProcessEligibilityService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class AutoPullEnrolleesFromAthena extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull eligible patients from Athena API.';
    protected $options;
    protected $service;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'athena:autoPullEnrolleesFromAthena {athenaPracticeId? : The Athena EHR practice id. `external_id` on table `practices`}
                                                                        {from? : From date yyyy-mm-dd}
                                                                        {to? : To date yyyy-mm-dd}
                                                                        {offset? : Offset results from athena api using number of target patients in the table}';

    /**
     * Create a new command instance.
     */
    public function __construct(ProcessEligibilityService $service)
    {
        parent::__construct();

        $this->service = $service;

        $this->options = [
            'filterProblems'      => true,
            'filterInsurance'     => false,
            'filterLastEncounter' => false,
        ];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $to   = Carbon::now()->format('y-m-d');
        $from = Carbon::now()->subMonth()->format('y-m-d');

        $offset = true;

        if ($this->argument('offset')) {
            $offset = $this->argument('offset');
        }

        if ($this->argument('from')) {
            $from = $this->argument('from');
        }

        if ($this->argument('to')) {
            $to = $this->argument('to');
        }

        if ($this->argument('athenaPracticeId')) {
            $practices = Practice::whereHas('ehr', function ($ehr) {
                $ehr->where('name', 'Athena');
            })
                ->where('external_id', $this->argument('athenaPracticeId'))
                ->get();
        } else {
            $practices = Practice::whereHas('ehr', function ($ehr) {
                $ehr->where('name', 'Athena');
            })
                ->whereHas('settings', function ($settings) {
                    $settings->where('api_auto_pull', 1);
                })
                ->get();
        }

        if (0 == $practices->count()) {
            if (isProductionEnv()) {
                sendSlackMessage(
                    '#parse_enroll_import',
                    "No Practices with checked 'api-auto-pull' setting were found for the weekly Athena Data Pull."
                );
            } else {
                return null;
            }
        }

        foreach ($practices as $practice) {
            $batch = $this->service->createBatch(EligibilityBatch::ATHENA_API, $practice->id, $this->options);

            Artisan::call('athena:getPatientIdFromLastYearAppointments', [
                'athenaPracticeId' => $practice->external_id,
                'from'             => $from,
                'to'               => $to,
                'offset'           => $offset,
                'batchId'          => $batch->id,
            ]);

            Artisan::call('athena:DetermineTargetPatientEligibility', ['batchId' => $batch->id]);
        }
    }
}
