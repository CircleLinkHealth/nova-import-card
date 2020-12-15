<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\SelfEnrollment\Jobs\CreateSurveyOnlyUserFromEnrollee;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Console\Command;

class UpdateCameronEnrolleeProviders extends Command
{
    const BRANDY_GERMAN_PROVIDER_USER_ID = '45405';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update provider_id for Cameron auto enrolment enrollees to be invited.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:cameron_enrollees_provider';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $practiceId  = Practice::whereName('cameron-memorial')->firstOrFail()->id;
        $providerId  = User::whereId(self::BRANDY_GERMAN_PROVIDER_USER_ID)->firstOrFail()->id;
        $enrolleeIds = $this->getEnrolleesToUpdate();

        $enrollees = Enrollee::where('practice_id', $practiceId)
            ->whereIn('id', $enrolleeIds);

        $updated = $enrollees->update([
            'provider_id' => $providerId,
        ]);

        if ( ! $updated) {
            $this->warn('Failed to update Enrollee provider_id.');

            return;
        }

        $enrollees->each(function ($enrollee) {
            CreateSurveyOnlyUserFromEnrollee::dispatch($enrollee);
        }, 100);
    }

    private function getEnrolleesToUpdate()
    {
        return [
            '343000',
            '343040',
            '343089',
            '343094',
            '343095',
        ];
    }
}
