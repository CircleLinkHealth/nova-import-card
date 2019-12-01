<?php

namespace App\Console\Commands;

use App\Jobs\GeneratePatientReportsJob;
use CircleLinkHealth\Customer\Entities\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class QueueGenerateProviderReports extends Command
{
    /**
     * Patient Ids to attempt to create provider reports for.
     *
     * @var array
     */
    protected $patientIds;

    /**
     * Date to specify for which survey instances to generate Provider Reports for.
     *
     * @var Carbon
     */
    protected $date;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:providerReport {patientIds : comma separated.} {date? : in format YYYY-MM-DD} {--debug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Provider Report(s).';

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

        $patientIds = $this->argument('patientIds') ?? null;
        if ($patientIds) {
            $patientIds = explode(',', $patientIds);
        } else {
            $patientIds = [];
        }

        $this->patientIds = $patientIds;
//        $this->date       = $this->argument('date')
//            ? Carbon::parse($this->argument('date'))
//            : Carbon::now();

        $debug = $this->option('debug');
        foreach ($this->patientIds as $patientId){
            GeneratePatientReportsJob::dispatch($patientId, $this->date, $debug)->onQueue('high');
        }
    }
}
