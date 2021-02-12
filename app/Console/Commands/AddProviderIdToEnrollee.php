<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Eligibility\CcdaImporter\CcdaImporterWrapper;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use CircleLinkHealth\SharedModels\Entities\PracticePull\Demographics;
use CircleLinkHealth\SharedModels\Entities\SupplementalPatientData;
use CircleLinkHealth\SharedModels\Search\Patients\SupplementalPatientDataUser;
use Illuminate\Console\Command;

class AddProviderIdToEnrollee extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fill enrollee provider ID';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:fill_enrollee_provider_id {--practice=} {--status=}';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Enrollee::whereNull('provider_id')
            ->when( ! empty($this->option('practice')), function ($q) {
                $q->where('practice_id', $this->option('practice'));
            })
            ->when( ! empty($this->option('status')), function ($q) {
                $q->where('status', $this->option('status'));
            })
            ->eachById(function (Enrollee $e) {
                $this->fromEnrolleeReferringProvider($e);
                if ($e->provider_id) {
                    return;
                }
                $this->fromPracticePull($e);
                if ($e->provider_id) {
                    return;
                }
                $this->fromSupplementalData($e);
            }, 50);

        return 0;
    }

    private function fromEnrolleeReferringProvider(Enrollee &$e)
    {
        if ( ! $e->referring_provider_name) {
            return;
        }
        $e->provider_id = optional(CcdaImporterWrapper::mysqlMatchProvider($e->referring_provider_name, $e->practice_id))->id;
        if ($e->isDirty()) {
            $e->save();
        }
    }

    private function fromPracticePull(Enrollee &$e)
    {
        $dem = Demographics::whereNotNull('referring_provider_name')
            ->where(function ($q) use ($e) {
                               $q->where(function ($q) use ($e) {
                                   $q->where('eligibility_job_id', '=', $e->eligibility_job_id)
                                       ->whereNotNull('eligibility_job_id');
                               })
                                   ->orWhere([
                                       ['mrn', '=', $e->mrn],
                                       ['first_name', '=', $e->first_name],
                                       ['last_name', '=', $e->last_name],
                                       ['dob', '=', $e->dob],
                                   ]);
                           })->first();
        if ($dem) {
            $e->referring_provider_name = $dem->referring_provider_name;
            if ($dem->billing_provider_user_id) {
                $e->provider_id = $dem->billing_provider_user_id;
                if ( ! empty($e->provider_id)) {
                    $e->save();

                    return;
                }
            }
            if ( ! $e->provider_id) {
                $this->fromEnrolleeReferringProvider($e);
            }
        }
    }
    
    private function fromSupplementalData(Enrollee $e)
    {
        $sup = SupplementalPatientData::forPatient($e->practice_id, $e->first_name, $e->last_name, $e->dob);
        if ($sup) {
            $e->referring_provider_name = $sup->provider;
            if ($sup->billing_provider_user_id) {
                $e->provider_id = $sup->billing_provider_user_id;
                if ( ! empty($e->provider_id)) {
                    $e->save();
                
                    return;
                }
            }
            if ( ! $e->provider_id) {
                $this->fromEnrolleeReferringProvider($e);
            }
        }
    }
}
