<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Console;


use Illuminate\Console\Command;

class ReimportMultiplePatientMedicalRecords extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reimport multiple patient data from medical records decided by this command. To be used for patients that did not import correctly.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'patient:recreate-multiple {patientUserIds : comma delimited} {initiatorUserId?} {--clear} {--without-transaction} {--clear-ccda}';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $ids = explode(',', $this->argument('patientUserIds'));

        if (! empty($ids)){
            foreach ($ids as $id){
                ReimportPatientMedicalRecord::for($id, $this->argument('initiatorUserId'), 'queue', [
                    '--clear' => (string) $this->option('clear'),
                    '--without-transaction' => (string) $this->option('without-transaction'),
                    '--clear-ccda' => (string) $this->option('clear-ccda')
                ]);
            }
        }
    }
}