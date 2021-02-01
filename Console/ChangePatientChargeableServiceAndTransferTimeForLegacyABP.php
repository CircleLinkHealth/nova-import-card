<?php

namespace CircleLinkHealth\CcmBilling\Console;

use Carbon\Carbon;
use CircleLinkHealth\Customer\AppConfig\PatientSupportUser;
use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Console\Command;
use CircleLinkHealth\CcmBilling\Jobs\ChangePatientChargeableServiceAndTransferTimeForLegacyABP as Job;

/**
 * @deprecated
*/
class ChangePatientChargeableServiceAndTransferTimeForLegacyABP extends Command
{
    protected Carbon $month;

    protected array $patientIds;

    protected ChargeableService $fromCs;
    protected ChargeableService $toCs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:patient-change-cs-and-time {patientIds : Comma delimited Patient Ids} 
                                                               {month : YYYY-MM-DD} 
                                                               {fromCs : ID, user friendly name or CS code} 
                                                               {toCs : ID, user friendly name or CS code} 
                                                          ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change specific patient CS to another and transfer time from that CS to the other, for a specific month, for Legacy Billing (Inc. PMS)';

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
        try {
            $this->setMonth();
        } catch (\Throwable $throwable) {
            $this->error("Invalid month input: {$this->argument('month')}");
            return;
        }

        try {
            $this->setServices();
        } catch (\Throwable $throwable) {
            $this->error("Invalid CS input: {$throwable->getMessage()}");
            return;
        }

        $this->setPatientIds();


        Job::dispatch(
            $this->patientIds,
            $this->month,
            $this->fromCs->id,
            $this->toCs->id,
            )
            ->onQueue(getCpmQueueName(CpmConstants::LOW_QUEUE));
    }

    private function getChargeableService($input): ChargeableService
    {
        if (is_numeric($input)) {
            return ChargeableService::findOrFail($input);
        }

        return ChargeableService::where('code', $input)
                                ->orWhere('display_name', strtoupper($input))
                                ->firstOrFail();
    }

    private function setServices(): void
    {
        $this->fromCs = $this->getChargeableService($this->argument('fromCs'));
        $this->toCs   = $this->getChargeableService($this->argument('toCs'));
    }

    private function setPatientIds(): void
    {
        $this->patientIds = collect(explode(',', $this->argument('patientIds')))
            ->map(function ($id) {
                $numId = (int)$id;

                if ((string)$numId !== $id) {
                    $this->info("Invalid ID: '$id'. Will not process patient");

                    return null;
                }

                return $numId;
            })->filter()
            ->toArray();
    }

    private function setMonth(): void
    {
        $this->month = Carbon::createFromFormat('Y-m-d', $this->argument('month'))->startOfMonth();
    }
}
