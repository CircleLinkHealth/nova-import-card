<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Console\Command;

class CheckForDraftCarePlans extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for care plans that are draft and are pending CLH QA/approval';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:draft-care-plans {--force-notify}';

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
     * @return void
     */
    public function handle()
    {
        $count = User::ofType('participant')
            ->whereHas('carePlan', function ($q) {
                $q->whereStatus('draft');
            })
            ->count();

        if ($count) {
            // $this->info($count);
            sendSlackMessage('#carecoach_ops', "$count Care Plans require Ops QA/Approval", $this->option('force-notify'));
        }
    }
}
