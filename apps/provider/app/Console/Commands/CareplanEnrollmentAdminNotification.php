<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Notifications\SendAssessmentNotification;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use Illuminate\Console\Command;

class CareplanEnrollmentAdminNotification extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notification to CLH admins about careplan enrollments done on the previous day';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enrollment:notification';

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
        $admins = User::ofType('administrator')->get();

        CarePlan::where('provider_date', '>=', Carbon::yesterday())
            ->has('patient.carePlanAssessment')
            ->with('patient.carePlanAssessment')
            ->get()
            ->map(function ($c) use ($admins) {
                if ($c->patient->carePlanAssessment) {
                    $admins->map(function ($user) use ($c) {
                        $user->notify(new SendAssessmentNotification($c->patient->carePlanAssessment));
                    });
                }
            });
    }
}
