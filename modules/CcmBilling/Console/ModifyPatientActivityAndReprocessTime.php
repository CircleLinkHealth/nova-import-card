<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Console;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Jobs\ModifyPatientActivityAndReprocessTime as Job;
use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use Illuminate\Console\Command;

class ModifyPatientActivityAndReprocessTime extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change specific patient CS to another and transfer time from that CS to the other, for a specific month';

    protected ?int $fromCsId;
    protected Carbon $month;

    protected array $patientIds;

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
    protected string $toCsCode;

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

        $this->filterAndSetPatientIds();

        if (empty($this->patientIds)) {
            $this->warn('Aborting Time Processing, no valid patient IDs left');

            return;
        }

        Job::dispatch(
            $this->patientIds,
            $this->month,
            $this->fromCsId,
            $this->toCsCode,
        )->onQueue(getCpmQueueName(CpmConstants::LOW_QUEUE));
    }

    private function filterAndSetPatientIds(): void
    {
        $this->patientIds = collect(explode(',', $this->argument('patientIds')))
            ->map(function ($id) {
                $numId = (int) $id;

                if ((string) $numId !== $id) {
                    $this->info("Invalid ID: '$id'. Will not process patient");

                    return null;
                }

                return $numId;
            })->filter()
            ->toArray();
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

    private function setMonth(): void
    {
        $this->month = Carbon::createFromFormat('Y-m-d', $this->argument('month'))->startOfMonth();
    }

    private function setServices(): void
    {
        $fromCsInput = $this->argument('fromCs');

        $this->fromCsId = strtolower($fromCsInput) === 'null' ? null : $this->getChargeableService($fromCsInput)->id;
        $this->toCsCode   = $this->getChargeableService($this->argument('toCs'))->code;
    }
}
