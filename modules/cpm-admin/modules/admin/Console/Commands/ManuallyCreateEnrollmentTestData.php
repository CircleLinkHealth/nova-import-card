<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Console\Commands;

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\Entities\EnrollmentInvitationLetter;
use CircleLinkHealth\Eligibility\SelfEnrollment\Console\Commands\PrepareDataForReEnrollmentTestSeeder;
use Illuminate\Console\Command;

class ManuallyCreateEnrollmentTestData extends Command
{
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

        if (isProductionEnv()) {
            $this->warn('You cannot execute this action in production environment');

            return 'You cannot execute this action in production environment';
        }

        if (is_null($practiceName)) {
            $this->warn('Practice input is required');

            return 'Practice input is required';
        }

        $practice = Practice::whereName($practiceName)->first();

        if ( ! $practice) {
            $this->error("$practiceName practice model not found.");

            return "$practiceName practice model not found.";
        }

        $letter = EnrollmentInvitationLetter::wherePracticeId($practice->id)->first();

        if ( ! $letter) {
            $this->error("$practiceName practice model not found.");

            return "$practiceName practice model not found.";
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
