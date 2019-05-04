<?php

namespace App\Console\Commands;

use App\Jobs\GenerateProviderReport;
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
    protected $signature = 'reports:providerReport {date? : in format YYYY-MM-DD} {patientIds? : comma separated. leave empty to attempt to generate for all}';

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

//        $patientIds = $this->argument('patientIds') ?? null;
//        if ($patientIds) {
//            $patientIds = explode(',', $patientIds);
//        } else {
//            $patientIds = User::ofType('participant')
//                              ->pluck('id')
//                              ->all();
//        }
//
//        $this->patientIds = $patientIds;
//        $this->date       = $this->argument('date')
//            ? Carbon::parse($this->argument('date'))
//            : Carbon::now();

    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        foreach ($this->patientIds as $patientId){
            GenerateProviderReport::dispatch($patientId, $this->date)->onQueue('high');
        }
    }
}
