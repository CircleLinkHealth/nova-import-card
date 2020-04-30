<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Console\Command;

class ReimportCcdasFromEnrolleesUsingMrn extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reimport all Enrollees for a practice using CCDAs matched from Enrollee MRN';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reimport:enrollees {practiceId} {--ej} {--ccd}';

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
        Enrollee::where('practice_id', $this->argument('practiceId'))
            ->with(['user', 'eligibilityJob', 'practice'])
            ->chunkById(
                100,
                function ($enrollees) {
                    $enrollees->each(
                        function (Enrollee $e) {
                            $this->showPreMessage($e);
                            $this->updateOrCreateCarePlan(
                                $this->medicalRecord($e)
                            );
                        }
                    );
                }
            );
    }

    private function linkCcdaUsingMrn(Enrollee $e): ?Ccda
    {
        $ccda = Ccda::withTrashed()->where('practice_id', $this->argument('practiceId'))->where(
            'patient_mrn',
            $e->mrn
        )->orderBy('deleted_at')->first();

        if ($ccda) {
            $e->medical_record_id   = $ccda->id;
            $e->medical_record_type = Ccda::class;
            $e->save();

            if ( ! is_null($ccda->deleted_at)) {
                $ccda->restore();
            }

            return $ccda;
        }

        return null;
    }

    private function medicalRecord(Enrollee $e)
    {
        if ($this->option('ccd')) {
            return $this->linkCcdaUsingMrn($e);
        }

//        if ($this->option('ej')) {
//            return $this->createCcdaFromEligibilityJob($e);
//        }
    }

    private function showPreMessage(Enrollee $e)
    {
        $msg = "Re-importing enrollee:$e->id";

        if ($e->user_id) {
            $msg .= ":user:$e->user_id";
        }

        if ($e->eligibility_job_id) {
            $msg .= ":eJ:$e->eligibility_job_id";
        }

        $this->warn($msg);
    }
}
