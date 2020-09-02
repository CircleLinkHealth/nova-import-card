<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\CcdaImporter\Hooks;

use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\CcdaImporter\BaseCcdaImportHook;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportPatientInfo;
use CircleLinkHealth\Eligibility\Entities\SupplementalPatientData;
use CircleLinkHealth\Eligibility\NBISupplementaryDataNotFound;
use Illuminate\Support\Facades\Notification;

class ReplaceFieldsFromSupplementaryData extends BaseCcdaImportHook
{
    const CUSTOMER_CPM_ALERTS_SLACK_CHANNEL_KEY = '#customer-cpm-alerts';
    const IMPORTING_LISTENER_NAME               = 'import_from_supplemental_patient_data';

    const RECEIVES_SUPPL_DATA_EXCEPTIONS_NOTIFICATIONS = 'receives_supplementary_data_exceptions_notifications';

    public function run()
    {
        $dataFromPractice = SupplementalPatientData::where('first_name', 'like', "{$this->patient->first_name}%")
            ->where('last_name', $this->patient->last_name)
            ->where('dob', $this->patient->patientInfo->birth_date)
            ->where('practice_id', $this->patient->program_id)
            ->whereHas('practice', function ($q) {
                $q->hasImportingHookEnabled(ImportPatientInfo::HOOK_IMPORTING_PATIENT_INFO, ReplaceFieldsFromSupplementaryData::IMPORTING_LISTENER_NAME)
                    ->where('id', $this->patient->program_id)
                ;
            })
            ->first();

        if ( ! $dataFromPractice) {
            self::sendPatientNotFoundSlackAlert($this->patient->id, $this->patient->program_id);

            $recipients = AppConfig::pull(self::RECEIVES_SUPPL_DATA_EXCEPTIONS_NOTIFICATIONS, []);

            foreach ($recipients as $recipient) {
                Notification::route('mail', $recipient)
                    ->notify(new NBISupplementaryDataNotFound($this->patient));
            }
        }

        if (optional($dataFromPractice)->mrn && is_array($this->payload)) {
            $this->payload['mrn_number'] = $dataFromPractice->mrn;
        }

        return $this->payload;
    }

    public static function sendPatientNotFoundSlackAlert(int $patientId, ?int $practiceId)
    {
        $key = "ReplaceFieldsFromSupplementaryDataPatientMRNNotFound:$patientId";

        if ($practiceId) {
            $practice = Practice::hasImportingHookEnabled(ImportPatientInfo::HOOK_IMPORTING_PATIENT_INFO, ReplaceFieldsFromSupplementaryData::IMPORTING_LISTENER_NAME)
                ->findOrFail($practiceId);
        }

        if ( ! isset($practice)) {
            $practice = User::ofType('participant')
                ->with('primaryPractice')
                ->whereHas('primaryPractice', function ($q) {
                    $q->hasImportingHookEnabled(ImportPatientInfo::HOOK_IMPORTING_PATIENT_INFO, ReplaceFieldsFromSupplementaryData::IMPORTING_LISTENER_NAME);
                })
                ->firstOrFail()
                ->primaryPractice;
        }

        if ( ! \Cache::has($key)) {
            $slackChannel = \Cache::remember(self::CUSTOMER_CPM_ALERTS_SLACK_CHANNEL_KEY, 2, function () {
                return AppConfig::pull(self::CUSTOMER_CPM_ALERTS_SLACK_CHANNEL_KEY);
            });
            $handles           = AppConfig::pull('supplemental_patient_data_replacement_alerts_slack_watchers', '');
            $patientUrl        = route('patient.demographics.show', ['patientId' => $patientId]);
            $patientProfileUrl = "<$patientUrl|this patient>";
            $novaUrl           = url('/superadmin/resources/supplemental-patient-data-resources');
            $novaLink          = "<$novaUrl|{$practice->display_name}'s supplementary MRN list>";

            if ($slackChannel) {
                sendSlackMessage(
                    $slackChannel,
                    "$handles URGENT! Could not find $patientProfileUrl in $novaLink. All {$practice->display_name} MRNs need to be replaced. Please add the correct MRN for this patient in $novaLink. The system will replace the MRN in patient's chart with the MRN you input.",
                    true
                );
            }

            \Cache::put($key, Carbon::now()->toDateTimeString(), 60 * 12);
        }
    }
}
