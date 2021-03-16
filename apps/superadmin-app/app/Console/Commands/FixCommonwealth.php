<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Eligibility\Services\AthenaAPI\Jobs\PullProvider;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Console\Command;

class FixCommonwealth extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Some Commonwealth patients do not have the correct providers';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:commonwealth';

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
        Enrollee::wherePracticeId(232)
            ->whereIn('status', [
                Enrollee::TO_CALL,
                Enrollee::UNREACHABLE,
            ])
            ->whereDoesntHave('user.patientInfo', function ($q) {
                $q->enrolled();
            })
            ->without('user.roles.perms')
            ->orderByDesc('id')
            ->each(
                function ($enrollee) {
                    $this->warn("Start Enrollee[$enrollee->id]");
                    PullProvider::dispatch($enrollee->id);
                },
                500
            );
    }
}
