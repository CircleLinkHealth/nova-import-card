<?php

namespace App\Console\Commands;

use App\Jobs\SelfEnrollmentEnrollees;
use App\Nova\Actions\SelfEnrollmentManualInvite;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Console\Command;

class SelfEnrollmentManualInviteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'self-enrollment:invite {enrolleeId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        SelfEnrollmentEnrollees::dispatch($model);
    }
}
