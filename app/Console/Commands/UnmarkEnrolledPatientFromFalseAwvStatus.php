<?php

namespace App\Console\Commands;

use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Console\Command;

class UnmarkEnrolledPatientFromFalseAwvStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'patients:unmark-awv';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Some patients were wrongly marked as AWV during enrollment';

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
     * @return int
     */
    public function handle()
    {
        User::with(['primaryPractice.chargeableServices'])
            ->whereHas('primaryPractice', function ($p){
                //exclude test awv patients
                $p->where('is_demo', false);
            })
            ->whereHas('patientInfo', function ($pi){
                $pi->where('is_awv', 1);
            })
            ->each(function (User $user){
                if ($user
                    ->primaryPractice
                    ->chargeableServices
                    ->whereIn('code', [ChargeableService::AWV_SUBSEQUENT, ChargeableService::AWV_INITIAL])
                    ->isNotEmpty()){
                    return;
                }

                $info = $user->patientInfo;
                $info->is_awv = 0;
                $info->save();

                $this->info("Unmarking awv for Patient: {$user->id}");
            });

    }
}
