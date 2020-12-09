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

        $enrollees = Enrollee::where('practice', $practiceId)
            ->whereIn('id', $enrolleeIds);

        $updated = $enrollees->update([
            'provider_id' => $providerId,
        ]);

        if ( ! $updated) {
            $this->warn('Failed to update Enrollee provider_id.');

            return;
        }

        foreach ($enrollees as $enrollee) {
            CreateSurveyOnlyUserFromEnrollee::dispatch($enrollee);
        }
    }

    private function getEnrolleesToUpdate()
    {
        return [
            '342415',
            '342423',
            '342449',
            '342506',
            '342517',
            '342532',
            '342557',
            '342595',
            '342601',
            '342603',
            '342613',
            '342629',
            '342637',
            '342651',
            '342680',
            '342683',
            '342687',
            '342692',
            '342703',
            '342717',
            '342732',
            '342734',
            '342747',
            '342748',
            '342756',
            '342757',
            '342767',
            '342779',
            '342782',
            '342793',
            '342800',
            '342825',
            '342826',
            '342828',
            '342838',
            '342842',
            '342867',
            '342871',
            '342888',
            '342906',
            '342911',
            '342918',
            '342922',
            '342987',
            '343000',
            '343040',
            '343089',
            '343094',
            '343095',
        ];
    }
}
