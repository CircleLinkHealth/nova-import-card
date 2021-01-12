<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Eligibility\Database\Seeders\GenerateCalvaryClinicLetter;
use CircleLinkHealth\Eligibility\Database\Seeders\GenerateCommonwealthPainAssociatesPllcLetter;
use CircleLinkHealth\Eligibility\Database\Seeders\GenerateDavisCountyLetter;
use CircleLinkHealth\Eligibility\Database\Seeders\GenerateWoodlandInternistsClinicLetter;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateEnrolmentLettersSignatoryName extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update enrolment letters signatory names';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:enrolmentLettersSignatoryName';

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
        $practicesWithSignatoryConstants = [
            'commonwealth-pain-associates-pllc' => GenerateCommonwealthPainAssociatesPllcLetter::PRACTICE_SIGNATORY_NAME,
            'woodlands-internists-pa'           => GenerateWoodlandInternistsClinicLetter::PRACTICE_SIGNATORY_NAME,
            'calvary-medical-clinic'            => GenerateCalvaryClinicLetter::PRACTICE_SIGNATORY_NAME,
            'davis-county'                      => GenerateDavisCountyLetter::PRACTICE_SIGNATORY_NAME,
        ];

        $isDemo = ! isProductionEnv();

        $practices = DB::table('practices')
            ->whereIn('name', array_keys($practicesWithSignatoryConstants))
            ->where('is_demo', '=', boolval($isDemo))
            ->get();

        $practiceIdsWithSignatoryName = $practices->mapWithKeys(function ($practice) use ($practicesWithSignatoryConstants) {
            return [
                $practice->id => Arr::pull($practicesWithSignatoryConstants, $practice->name),
            ];
        })->toArray();

        if (4 !== $practices->count() || 4 !== count($practiceIdsWithSignatoryName)) {
            Log::error('Practices should have been 4 in total. Less found');
            $this->error('Practices should have been 4 in total. Less found.');

            return;
        }

        foreach ($practiceIdsWithSignatoryName as $practiceId => $signatoryConstantName) {
            $letterUpdated = DB::table('enrollment_invitation_letters')
                ->where('practice_id', $practiceId)
                ->update([
                    'signatory_name' => $signatoryConstantName,
                ]);

            if ( ! $letterUpdated) {
                Log::error("Letter with practice_id $practiceId did not updated");
                $this->error("Letter with practice_id $practiceId did not updated");
            }

            $this->info("Letter with practice_id $practiceId updated successfully");
        }
    }
}
