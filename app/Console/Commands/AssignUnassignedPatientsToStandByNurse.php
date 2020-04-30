<?php

namespace App\Console\Commands;

use App\Call;
use CircleLinkHealth\Customer\AppConfig\StandByNurseUser;
use Illuminate\Console\Command;

class AssignUnassignedPatientsToStandByNurse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assign:unassigned-patients-to-stand-by-nurse';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign unassigned patients to standby nurse';

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
        $updated = Call::whereHas('inboundUser', function ($q){
            $q->isNotDemo()->whereHas('patientInfo', function($q) {
                return $q->enrolled();
            });
        })->where('status', Call::SCHEDULED)->unassigned()->update([
            'outbound_cpm_id' => StandByNurseUser::id()
        ]);
        
        $this->line($updated. ' rows updated.');
    }
}
