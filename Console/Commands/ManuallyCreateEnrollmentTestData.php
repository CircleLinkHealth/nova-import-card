<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SelfEnrollment\Console\Commands;

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use CircleLinkHealth\SelfEnrollment\Database\Seeders\GenerateDemoLetter;
use CircleLinkHealth\SelfEnrollment\Entities\EnrollmentInvitationLetter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;

class ManuallyCreateEnrollmentTestData extends Command
{
    use UserHelpers;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Triggers 'PrepareDataForReEnrollmentTestSeeder'.
    Accepts practice name as parameter like: 'mario-bros-clinic'";
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:selfEnrollmentTestData {practiceName}';

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
     * @throws \Exception
     *
     * @return int
     */
    public function handle()
    {
        $practiceName = $this->argument('practiceName') ?? null;

        if (isProductionEnv() && $practiceName !== GenerateDemoLetter::DEMO_PRACTICE_NAME) {
            $this->warn('You cannot execute this action in production environment');

            return 'You cannot execute this action in production environment';
        }

        if (is_null($practiceName)) {
            $this->warn('Practice input is required');
            return 'Practice input is required';
        }

        $practice = Practice::whereName($practiceName)->first();

        if (! $practice && ! App::environment('production')){
            $this->info("Practice $practiceName to test not found. Creating practice with Location now...");
            $practice = $this->selfEnrollmentTestPractice($practiceName);
            $this->selfEnrollmentTestLocation($practice->id, $practiceName);
        }

        $letter = EnrollmentInvitationLetter::wherePracticeId($practice->id)->first();

        if ( ! $letter && ! App::environment('production')) {
            $this->info("$practiceName practice letter not found. Generating Letter for $practiceName now...");
            Artisan::call(RegenerateSelfEnrollmentLetters::class, ['--forPractice' => $practiceName]);
        }

        $uiRequestsForThisPractice = '';

        if (EnrollmentInvitationLetter::DEPENDED_ON_PROVIDER_GROUP === $letter->customer_signature_src) {
            $uiRequestsForThisPractice = EnrollmentInvitationLetter::DEPENDED_ON_PROVIDER_GROUP;
        }

        if (EnrollmentInvitationLetter::DEPENDED_ON_PROVIDER === $letter->customer_signature_src) {
            $uiRequestsForThisPractice = EnrollmentInvitationLetter::DEPENDED_ON_PROVIDER;
        }

        (new PrepareDataForReEnrollmentTestSeeder($practiceName, $uiRequestsForThisPractice))->run();
    }
}
