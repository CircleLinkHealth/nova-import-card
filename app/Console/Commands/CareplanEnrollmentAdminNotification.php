<?php

namespace App\Console\Commands;

use App\User;
use App\CarePlan;
use App\CareplanAssessment;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CareplanEnrollmentAdminNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enrollment:notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notification to CLH admins about careplan enrollments done on the previous day';

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
        $admins = User::ofType('administrator')->get();
        CarePlan::where('provider_date', '>=', Carbon::yesterday())->with('assessment')->map(function ($c) {
            if ($c->assessment) {
                $admins->map(function ($user) use ($c) {
                    $user->notify(new SendAssessmentNotification($c->assessment));
                });
            }
        });
    }
}
