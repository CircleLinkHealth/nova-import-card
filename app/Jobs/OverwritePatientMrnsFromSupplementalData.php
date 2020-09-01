<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Eligibility\CcdaImporter\Hooks\ReplaceFieldsFromSupplementaryData;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportPatientInfo;
use CircleLinkHealth\Eligibility\Entities\SupplementalPatientData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OverwritePatientMrnsFromSupplementalData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    const CUTOFF_DATE = '2019-07-17 00:00:00';
    
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Patient::with(
            'user'
        )->whereHas('user', function ($q) {
            $q->ofType('participant')
                ->whereHas('practices', function ($q) {
                    $q->hasImportingHookEnabled(ImportPatientInfo::HOOK_IMPORTING_PATIENT_INFO, ReplaceFieldsFromSupplementaryData::IMPORTING_LISTENER_NAME);
                });
        })->whereNotIn('mrn_number', function ($q) {
            $q->select('mrn')->from((new SupplementalPatientData())->getTable());
        })->where('created_at', '>', self::CUTOFF_DATE)
            ->each(function ($patientInfo) {
                $this->lookupAndReplaceMrn($patientInfo);
            });
    }

    private function lookupAndReplaceMrn(Patient $patientInfo)
    {
        $dataFromPractice = SupplementalPatientData::where('first_name', 'like', "{$patientInfo->user->first_name}%")
            ->where('last_name', $patientInfo->user->last_name)
            ->where('dob', $patientInfo->birth_date)
            ->where('practice_id', $patientInfo->user->program_id)
            ->whereHas('practice', function ($q) {
                $q->hasImportingHookEnabled(ImportPatientInfo::HOOK_IMPORTING_PATIENT_INFO, ReplaceFieldsFromSupplementaryData::IMPORTING_LISTENER_NAME);
            })
            ->first();

        if (optional($dataFromPractice)->mrn) {
            $patientInfo->mrn_number = $dataFromPractice->mrn;
            $patientInfo->save();

            return true;
        }

        ReplaceFieldsFromSupplementaryData::sendPatientNotFoundSlackAlert($patientInfo->user_id, $patientInfo->user->program_id);

        return false;
    }
}
