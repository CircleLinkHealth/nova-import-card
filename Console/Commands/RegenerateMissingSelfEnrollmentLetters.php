<?php

namespace CircleLinkHealth\SelfEnrollment\Console\Commands;

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\SelfEnrollment\Database\Seeders\GenerateCalvaryClinicLetter;
use CircleLinkHealth\SelfEnrollment\Database\Seeders\GenerateCameronLetter;
use CircleLinkHealth\SelfEnrollment\Database\Seeders\GenerateCommonwealthPainAssociatesPllcLetter;
use CircleLinkHealth\SelfEnrollment\Database\Seeders\GenerateDavisCountyLetter;
use CircleLinkHealth\SelfEnrollment\Database\Seeders\GenerateMarillacHealthLetter;
use CircleLinkHealth\SelfEnrollment\Database\Seeders\GenerateNbiLetter;
use CircleLinkHealth\SelfEnrollment\Database\Seeders\GenerateToledoClinicLetter;
use CircleLinkHealth\SelfEnrollment\Database\Seeders\GenerateWoodlandInternistsClinicLetter;
use CircleLinkHealth\SelfEnrollment\Database\Seeders\GenerateToledoSignatures;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class RegenerateMissingSelfEnrollmentLetters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:missingLetters {--forPractice=} {--forceUpdateAll}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Some Letters are missing from enrollment_invitation_letters.
    This command will look for them and then ran the appropriate seeder.
      {--forPractice} option = practice->name. If is set it will updateOrCreate the letter for given practice.
      {--forceUpdateAll} = UpdateOrCreate on ALL Letters
      --If options are left empty = Update or Create all Practices ONLY if their letter is missing.';

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
     * @return void
     */
    public function handle()
    {
        $practiceName = $this->option('forPractice');
        $forceUpdate = $this->option('forceUpdateAll');

        if ($forceUpdate){
            $practiceNames = collect($this->selfEnrollmentPractices());

            $practiceNames->each(function ($practiceName){
                    $this->info("Updating [$practiceName] Letter.");
                    $this->generateLetterFor($practiceName);
                });
                return;
            }


        if (! $practiceName){
            $this->checkAllPracticesLetters();
            $this->info("Done!");
            return;
        }

        try {
            $this->generateLetterFor($practiceName);
        }catch (\Exception $exception){
            $this->error($exception->getMessage());
            return;
        }

        $this->info("Letter for $practiceName generated!");
    }

    private function generateLetterFor(string $practiceName)
    {
        if ($practiceName === GenerateToledoSignatures::TOLEDO_CLINIC){
            Artisan::call('db:seed', ['--class' => GenerateToledoClinicLetter::class]);
            Artisan::call('db:seed', ['--class' => GenerateToledoSignatures::class]);
            return;
        }

        if ($practiceName === GenerateCommonwealthPainAssociatesPllcLetter::COMMON_WEALTH_NAME){
            Artisan::call('db:seed', ['--class' => GenerateCommonwealthPainAssociatesPllcLetter::class]);
            return;
        }

        if ($practiceName === GenerateCalvaryClinicLetter::CALVARY_PRACTICE_NAME){
            Artisan::call('db:seed', ['--class' => GenerateCalvaryClinicLetter::class]);
            return;
        }

        if ($practiceName === GenerateWoodlandInternistsClinicLetter::WOODLANDS_PRACTICE_NAME){
            Artisan::call('db:seed', ['--class' => GenerateWoodlandInternistsClinicLetter::class]);
            return;
        }

        if ($practiceName === GenerateDavisCountyLetter::DAVIS_PRACTICE_NAME){
            Artisan::call('db:seed', ['--class' => GenerateDavisCountyLetter::class]);
            return;
        }

        if ($practiceName === GenerateMarillacHealthLetter::MARILLAC_PRACTICE_NAME){
            Artisan::call('db:seed', ['--class' => GenerateMarillacHealthLetter::class]);
            return;
        }

        if ($practiceName === GenerateCameronLetter::CAMERON_PRACTICE_NAME){
            Artisan::call('db:seed', ['--class' => GenerateCameronLetter::class]);
            return;
        }

        if ($practiceName === GenerateNbiLetter::NBI_PRACTICE_NAME){
            Artisan::call('db:seed', ['--class' => GenerateNbiLetter::class]);
            return;
        }
    }

    private function checkAllPracticesLetters()
    {
        $practiceNames = $this->selfEnrollmentPractices();

        $practicesMissingLetter = $this->getPracticesMissingLetter($practiceNames);

        if ($practicesMissingLetter->isNotEmpty()){
            $practiceNamesMissingLetter = $practicesMissingLetter->pluck('name');
            $message = implode(', ', $practiceNamesMissingLetter->toArray());
            $this->info("Letter not found for $message. Generating Letter now...");
            $practiceNamesMissingLetter->each(function ($practiceName){
               $this->generateLetterFor($practiceName);
            });
            return;
        }

        $this->info("All Practices have Self Enrollment Letters. Nothing done here!");
    }

    private function selfEnrollmentPractices()
    {
        return [
            GenerateToledoSignatures::TOLEDO_CLINIC,
            GenerateCommonwealthPainAssociatesPllcLetter::COMMON_WEALTH_NAME,
            GenerateCalvaryClinicLetter::CALVARY_PRACTICE_NAME,
            GenerateWoodlandInternistsClinicLetter::WOODLANDS_PRACTICE_NAME,
            GenerateDavisCountyLetter::DAVIS_PRACTICE_NAME,
            GenerateMarillacHealthLetter::MARILLAC_PRACTICE_NAME,
            GenerateCameronLetter::CAMERON_PRACTICE_NAME,
            GenerateNbiLetter::NBI_PRACTICE_NAME,
        ];
    }

    /**
     * @param array $practiceNames
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    private function getPracticesMissingLetter(array $practiceNames)
    {
        return Practice::with('enrollmentLetter')
            ->whereDoesntHave('enrollmentLetter')
            ->whereIn('name', $practiceNames)
            ->select('name')
            ->get();
    }
}
