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
     * Year to specify for which survey instances to generate Provider Reports for.
     *
     * @var int
     */
    protected $year;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:providerReport {patientIds : comma separated.} {year?} {--debug}';

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
        $this->year       = $this->argument('year')
            ? intval($this->argument('year'))
            : Carbon::now()->year;

        $debug = $this->option('debug');
        foreach ($this->patientIds as $patientId){
            GeneratePatientReportsJob::dispatch($patientId, $this->year, $debug)->onQueue('awv-high');
        }
    }
}
