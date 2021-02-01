<?php

namespace CircleLinkHealth\CcmBilling\Console;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

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
                                                               {month: YYYY-MM-DD} 
                                                               {fromCs: ID, user friendly name or CS code} 
                                                               {toCs: ID, user friendly name or CS code} 
                                                               {--transfer-time=true}';

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
        } catch (\Throwable $throwable){
            $this->error("Invalid month input: {$this->argument('month')}");
        }

        try {
            $this->setServices();
        } catch (\Throwable $throwable){
            $this->error("Invalid CS input: {$throwable->getMessage()}");
        }

        $this->setPatientIds();


        User::ofType('participant')
            ->whereHas('patientInfo')
            ->with(['primaryPractice.chargeableServices',
                    'patientSummaries' => fn($pms) => $pms->where('month_year', $this->month) ])
            ->whereIn('id', $this->patientIds)
            ->each(function (User $patient){
                if ($patient->primaryPractice->chargeableServices->contains($this->toCs)){
                    $this->warn("Patient ($patient->id) Primary Practice ({$patient->primaryPractice->id}), does not have CS ({$this->toCs->id}). Cannot attach to patient.");
                    return;
                }

                //detach previous CS from PMS
                //attach new to PMS

                //transfer time

            });
    }

    private function getChargeableService($input): ChargeableService
    {
        if (is_numeric($input)){
            return ChargeableService::findOrFail($input);
        }

        return ChargeableService::where('code', $input)
            ->orWhere('display_name', strtoupper($input))
            ->firstOrFail();
    }

    private function setServices() : void
    {
        $this->fromCs = $this->getChargeableService($this->argument('fromCs'));
        $this->toCs = $this->getChargeableService($this->argument('toCs'));
    }

    private function setPatientIds():void
    {
        $this->patientIds = collect(explode(',', $this->argument('patientIds')))
            ->map(function($id){
                $numId = (int)$id;

                if ((string)$numId !== $id){
                    $this->info("Invalid ID: $id, will not process patient");
                    return null;
                }

                return $numId;
            })->filter()
        ->toArray();
    }

    private function setMonth() : void
    {
        $this->month = Carbon::createFromFormat('YYYY-MM-DD', $this->argument('month'))->startOfMonth();
    }
}
