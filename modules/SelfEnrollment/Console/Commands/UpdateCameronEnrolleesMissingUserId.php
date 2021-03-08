<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SelfEnrollment\Console\Commands;

use CircleLinkHealth\SelfEnrollment\Jobs\CreateSurveyOnlyUserFromEnrollee;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Console\Command;

class UpdateCameronEnrolleesMissingUserId extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run createSurveyOnlyUserFromEnrollee for Enrollees that are missing user_id.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:survey-user-cameron-enrollees';

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
     * @return int
     */
    public function handle()
    {
        $enrolleeIds = $this->enrolleeIds();

        Enrollee::whereIn('id', $enrolleeIds)
            ->whereNull('user_id')
            ->chunk(20, function ($enrollees) {
                $enrollees->each(function ($enrollee) {
                    CreateSurveyOnlyUserFromEnrollee::dispatch($enrollee);
                });
            });
    }

    private function enrolleeIds()
    {
        return [
            342414,
            342415,
            342423,
            342449,
            342506,
            342517,
            342532,
            342557,
            342595,
            342601,
            342603,
            342613,
            342629,
            342637,
            342651,
            342680,
            342683,
            342687,
            342692,
            342703,
            342717,
            342732,
            342734,
            342747,
            342748,
            342756,
            342757,
            342767,
            342779,
            342782,
            342793,
            342800,
            342825,
            342826,
            342828,
            342838,
            342842,
            342867,
            342871,
            342888,
            342906,
            342911,
            342918,
            342922,
            342987,
            343000,
            343040,
            343089,
            343094,
            343095,
            343098,
            343102,
            343114,
            343125,
            343128,
            343131,
            343135,
            343140,
            343159,
            343161,
            343187,
            343189,
            343196,
            343215,
            343236,
            343243,
            343258,
            343292,
            343299,
            343316,
            343321,
            343334,
            343355,
            343376,
            343405,
            343413,
            343416,
            343425,
            343429,
            343432,
            343442,
            343444,
            343450,
            343456,
            343469,
            343479,
            343509,
            343511,
            343525,
            343569,
            343608,
            343618,
            343621,
            343625,
            343635,
            343637,
            343655,
            343657,
            343663,
            343670,
            343698,
            343704,
            343728,
            343751,
            343767,
            343773,
            343785,
            343794,
            343799,
            343802,
            343814,
            343815,
            343821,
            343824,
            343825,
            343835,
            343849,
            343861,
            343885,
            343903,
            343911,
            343913,
            343942,
            343957,
            343974,
            343993,
            344016,
            344024,
            344038,
            344043,
            344055,
            344060,
            344071,
            344077,
            344089,
            344097,
            344111,
            344143,
            344153,
            344174,
            344181,
            344197,
            344275,
            344284,
        ];
    }
}
