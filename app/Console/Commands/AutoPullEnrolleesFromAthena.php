<?php

namespace App\Console\Commands;

use App\EligibilityBatch;
use App\Enrollee;
use App\Practice;
use App\Services\CCD\ProcessEligibilityService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class AutoPullEnrolleesFromAthena extends Command
{
    protected $options;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'athena:autoPullEnrolleesFromAthena {athenaPracticeId? : The Athena EHR practice id. `external_id` on table `practices`}
                                                                        {from? : From date yyyy-mm-dd}
                                                                        {to? : To date yyyy-mm-dd}
                                                                        {offset? : Offset results from athena api using number of target patients in the table}';

    protected $service;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ProcessEligibilityService $service)
    {
        parent::__construct();

        $this->service = $service;

        $this->options = ['filterProblems' => true,
                          "filterInsurance" => true,
                          "filterLastEncounter" => true];

    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $to   = Carbon::now()->format('y-m-d');
        $from = Carbon::now()->subWeek()->format('y-m-d');;
        $offset = false;

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
                                 ->where('auto_pull', 1)
                                 ->get();
        }else{
            $practices = Practice::whereHas('ehr', function ($ehr) {
                $ehr->where('name', 'Athena');
            })
                                 ->where('auto_pull', 1)
                                 ->get();
        }

        foreach ($practices as $practice) {

            Artisan::call('athena:getPatientIdFromLastYearAppointments', [
                'athenaPracticeId' => $practice->external_id,
                'from'            => $from,
                'to'              => $to,
                'offset'          => $offset,
            ]);

            Artisan::call('athena:DetermineTargetPatientEligibility');

            $enrollees = Enrollee::where('status', Enrollee::ELIGIBLE)
                                        ->get();

            $options = ["filterProblems" => true, "filterInsurance" => true, "filterLastEncounter" => true];

            $batch = $this->service->createBatch(EligibilityBatch::ATHENA_API, $practice->id, $this->options);

            if (app()->environment('worker')) {
                sendSlackMessage(' #parse_enroll_import',
                    "Eligibility Batch created.");
            }

        }
    }
}
