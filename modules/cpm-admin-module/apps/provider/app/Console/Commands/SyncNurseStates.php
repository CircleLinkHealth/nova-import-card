<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\State;
use Illuminate\Console\Command;

class SyncNurseStates extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add states to all nurses, according to the practices they have access to.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nurses:syncStates';

    /**
     * Create a new command instance.
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
        $states = State::get()
            ->pluck('id', 'code')
            ->all();

        Nurse::with('user.practices.locations')
            ->get()
            ->each(function ($n) use ($states) {
                $user = $n->user;

                if ( ! $user) {
                    return false;
                }

                $practices = $user
                    ->practices;

                if ( ! $practices) {
                    return false;
                }

                $stateIDs = $practices
                    ->map(function ($p) use ($states) {
                        return $p->locations
                            ->map(function ($l) use ($states) {
                                return $states[$l->state];
                            });
                    })
                    ->flatten()
                    ->unique()
                    ->all();

                $n->states()
                    ->syncWithoutDetaching($stateIDs);
            });
    }
}
