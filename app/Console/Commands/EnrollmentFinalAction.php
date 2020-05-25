<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\SelfEnrollment\Actions\UnreachablesFinalAction;
use App\SelfEnrollment\Jobs\DispatchSelfEnrollmentAction;
use Illuminate\Console\Command;

class EnrollmentFinalAction extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Final action on non responsive patients 4 days after initial invitation';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:enrollmentFinalAction';

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
        DispatchSelfEnrollmentAction::dispatch(UnreachablesFinalAction::class);
    }
}
