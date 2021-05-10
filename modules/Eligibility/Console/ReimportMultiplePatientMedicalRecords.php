<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Console;


use CircleLinkHealth\Customer\CpmConstants;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

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

        $params = [];

        if (! empty($ids)){
            foreach ($ids as $id){
                $command = "patient:recreate $id";

                if ($this->option('clear')){
                    $command .= ' --clear';
                }
                if ($this->option('without-transaction')){
                    $command .= ' --without-transaction';
                }
                if ($this->option('clear-ccda')){
                    $command .= ' --clear-ccda';
                }
                Artisan::queue($command)->onQueue(getCpmQueueName(CpmConstants::LOW_QUEUE));
            }
        }
    }
}