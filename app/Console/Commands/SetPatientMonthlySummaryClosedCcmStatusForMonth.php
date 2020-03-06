<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\NurseInvoices\Traits\DryRunnable;
use Illuminate\Console\Command;

class SetPatientMonthlySummaryClosedCcmStatusForMonth extends Command
{
    use DryRunnable;
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pms:set-closed-status {date? : the month we are counting for in format YYYY-MM-DD}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the CCM status of the patient for the given month.';
    /**
     * @var int
     */
    private $changedCount = 0;
    
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $argument = $this->argument('date') ?? null;
        
        $date = $argument
            ? Carbon::parse($argument)->startOfMonth()
            : Carbon::now()->startOfMonth();
        
        PatientMonthlySummary::orderBy('id')
                             ->whereMonthYear($date->toDateString())
                             ->with('patient.patientInfo')
                             ->chunk(
                                 500,
                                 function ($summaries) use ($date) {
                                     $summaries->each(
                                         function (PatientMonthlySummary $summary) use ($date) {
                                             $actualStatus = $summary->patient->patientInfo->getCcmStatusForMonth(
                                                 $date
                                             );
                                             if ($summary->closed_ccm_status !== $actualStatus) {
                                                 $this->warn(
                                                     "changing patient:{$summary->patient->id} summary:$summary->id"
                                                 );
                            
                                                 if ( ! $this->isDryRun()) {
                                                     $summary->closed_ccm_status = $actualStatus;
                                                     $summary->save();
                                                 }
                                             }
                                         }
                                     );
                                 }
                             );
        $this->info("{$this->changedCount} patient summaries changed.");
    }
}
