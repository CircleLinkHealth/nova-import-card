<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Http\Controllers\Enrollment\AutoEnrollmentCenterController;
use App\Jobs\SelfEnrollmentEnrollees;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Console\Command;

class SelfEnrollmentManualInviteCommand extends Command
{
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
    protected $signature = 'self-enrollment:invite {enrolleeId}';

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
        $model = Enrollee::find($this->argument('enrolleeId'));
        SelfEnrollmentEnrollees::dispatch($model, AutoEnrollmentCenterController::DEFAULT_BUTTON_COLOR);
    }
}
