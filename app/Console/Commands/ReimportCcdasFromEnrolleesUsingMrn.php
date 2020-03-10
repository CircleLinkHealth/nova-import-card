<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord;
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
    protected $signature = 'reimport:enrollees {practiceId}';

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
            ->with('user')
            ->chunkById(
                    100,
                    function ($enrollees) {
                        $enrollees->each(
                            function (Enrollee $e) {
                                $this->showPreMessage($e);
                                $this->updateOrCreateCarePlan(
                                    $this->reimport(
                                        $this->linkCcdaUsingMrn($e)
                                    )
                                );
                            }
                        );
                    }
                );
    }

    private function linkCcdaUsingMrn(Enrollee $e): ?Ccda
    {
        $ccda = Ccda::withTrashed()->where('practice_id', $this->argument('practiceId'))->where(
            'mrn',
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

    private function reimport(?Ccda $ccda): ?ImportedMedicalRecord
    {
        if ( ! $ccda) {
            return null;
        }

        if ($imr = $ccda->importedMedicalRecord()) {
            $ccda->import();

            return $imr;
        }

        return null;
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

    private function updateOrCreateCarePlan(?ImportedMedicalRecord $imr)
    {
        if ( ! $imr) {
            return null;
        }

        if ($imr) {
            if ($imr->patient()->exists()) {
                $imr->updateOrCreateCarePlan();
            }
        }
    }
}
