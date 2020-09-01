<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\CcdaImporter\Hooks\ReplaceFieldsFromSupplementaryData;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportPatientInfo;
use CircleLinkHealth\Eligibility\Entities\SupplementalPatientData;
use Illuminate\Console\Command;

class OverwritePatientMrnsFromSupplementalData extends Command
{
    const CUTOFF_DATE = '2019-07-17 00:00:00';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Overwrite data for NBI patients. This is a safe check in case we missed any.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nbi:mrn';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $result = Patient::with(
            'user'
        )->whereHas('user', function ($q) {
            $q->ofType('participant')
                ->whereHas('practices', function ($q) {
                    $q->hasImportingHookEnabled(ImportPatientInfo::HOOK_IMPORTING_PATIENT_INFO, ReplaceFieldsFromSupplementaryData::IMPORTING_LISTENER_NAME);
                });
        })->whereNotIn('mrn_number', function ($q) {
            $q->select('mrn')->from((new SupplementalPatientData())->getTable());
        })->where('created_at', '>', self::CUTOFF_DATE)->get()
            ->map(
                function ($patientInfo) {
                    $this->info("Checking User id: $patientInfo->user_id");

                    return [
                        'user_id'      => $patientInfo->user_id,
                        'was_replaced' => $this->lookupAndReplaceMrn($patientInfo),
                    ];
                }
            );

        $this->table(['user_id', 'was_replaced'], $result->all());
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
