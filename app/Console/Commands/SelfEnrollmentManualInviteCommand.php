<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Jobs\EnrollmentSeletiveInviteEnrollees;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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

        if (is_null($model->user_id)) {
            Log::warning("Enrollee [$model->id] has null user_id. this is unexpected at this point");

            return info("Enrollee [$model->id] has null user_id. this is unexpected at this point");
        }

        EnrollmentSeletiveInviteEnrollees::dispatch([$model->user_id]);
    }
}
