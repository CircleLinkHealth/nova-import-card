<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Algorithms\Calls\NurseFinder\NurseFinderEloquentRepository;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Console\Command;

class FixPatientsAssignedToNurseEthanErroneously extends Command
{
    const NURSE_ETHAN_USER_ID = 15354;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix';

    private NurseFinderEloquentRepository $repo;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(NurseFinderEloquentRepository $repo)
    {
        parent::__construct();
        $this->repo = $repo;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        User::with(['inboundCalls' => function ($q) {
            $q->whereIsCpmOutbound(true)
                ->whereStatus('scheduled');
        }])->ofActiveBillablePractice(false)
            ->whereHas('inboundCalls', function ($q) {
                $q->whereIsCpmOutbound(true)
                    ->whereStatus('scheduled');
            })->whereDoesntHave('patientNurseAsPatient')
            ->each(function ($u) {
                foreach ($u->inboundCalls as $call) {
                    if (is_numeric($call->inbound_cpm_id) && is_numeric($call->outbound_cpm_id)) {
                        $this->repo->assign($call->inbound_cpm_id, $call->outbound_cpm_id);
                    }
                }
            });

        return 0;
    }
}
