<?php

namespace App\Console\Commands;

use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\MedicalRecord;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use CircleLinkHealth\SharedModels\Entities\TabularMedicalRecord;
use Illuminate\Console\Command;

class ReimportCcdasFromEnrolleesUsingMrn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reimport:enrollees {practiceId} {--tmr}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reimport all Enrollees for a practice using CCDAs matched from Enrollee MRN';
    
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
                                        $this->option('tmr')
                                            ? $this->linkTMRUsingMrn($e)
                                            : $this->linkCcdaUsingMrn($e)
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
            'json->demographics->mrn_number',
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
    
    private function reimport(?MedicalRecord $mr): ?ImportedMedicalRecord
    {
        if ( ! $mr) {
            return null;
        }
        
        if ($imr = $mr->importedMedicalRecord()) {
            $mr->import();
            
            return $imr;
        }
        
        return null;
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
    
    private function linkTMRUsingMrn(Enrollee $e): ?TabularMedicalRecord
    {
        $tmr = TabularMedicalRecord::withTrashed()->where('practice_id', $this->argument('practiceId'))->where(
            'mrn',
            $e->mrn
        )->orderBy('deleted_at')->first();
        
        if ($tmr) {
            $e->medical_record_id   = $tmr->id;
            $e->medical_record_type = TabularMedicalRecord::class;
            $e->save();
            
            if ( ! is_null($tmr->deleted_at)) {
                $tmr->restore();
            }
            
            return $tmr;
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
}
