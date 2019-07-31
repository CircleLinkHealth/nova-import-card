<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Importer\CarePlanHelper;
use App\Models\PatientData\NBI\PatientData;
use CircleLinkHealth\Customer\Entities\Patient;
use Illuminate\Console\Command;

class OverwriteNBIPatientMRN extends Command
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
        $result = Patient::with('user')->whereHas('user', function ($q) {
            $q->ofType('participant')->whereHas('practices', function ($q) {
                $q->where('name', CarePlanHelper::NBI_PRACTICE_NAME);
            });
        })->whereNotIn('mrn_number', function ($q) {
            $q->select('mrn')->from((new PatientData())->getTable());
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
        $dataFromPractice = PatientData::where('first_name', 'like', "{$patientInfo->user->first_name}%")
            ->where('last_name', $patientInfo->user->last_name)
            ->where('dob', $patientInfo->birth_date)
            ->first();

        if (optional($dataFromPractice)->mrn) {
            $patientInfo->mrn_number = $dataFromPractice->mrn;
            $patientInfo->save();

            return true;
        }

        $patientUrl        = route('patient.demographics.show', ['patientId' => $patientInfo->user_id]);
        $patientProfileUrl = "<$patientUrl|this patient>";
        $novaUrl           = url('/superadmin/resources/n-b-i-patient-datas');
        $novaLink          = "<$novaUrl|NBI's supplementary MRN list>";
        sendSlackMessage('#nbi_rwjbarnabas', "@channel URGENT! Could not find $patientProfileUrl in $novaLink. All NBI MRNs need to be replaced. Please add the correct MRN for this patient in $novaLink. The system will replace the MRN in patient's chart with the MRN you input.", true);

        return false;
    }
}
