<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Call;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Console\Command;

class FixFalseDOBs extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix DOB imported on current date';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:dob';
    
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
        User::ofType('participant')->with('patientInfo')->whereDoesntHave(
            'inboundCalls',
            function ($q) {
                $q->where('status', Call::REACHED);
            }
        )->ofPractice(219)->chunkById(
            100,
            function ($tmr) {
                $tmr->each(
                    function (User $user) {
                        $e = Enrollee::where('user_id', $user->id)->first();
                        
                        if ( ! $e) {
                            return;
                        }
                        if ($e->dob->gte(Carbon::createFromDate(2000, 1, 1))) {
                            $e->dob = $e->dob->subYears(100)->toDateTimeString();
                            $e->save();
                        }
                        
                        if ($e->dob->toDateString() != $user->getBirthDate()) {
                            $user->patientInfo->birth_date = $e->dob;
                            
                            $user->patientInfo->save();
                        }
                    }
                );
            }
        );
    }
}
