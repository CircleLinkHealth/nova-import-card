<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\CcdaImporter\Hooks;

use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\CcdaImporter\BaseCcdaImportHook;
use CircleLinkHealth\Eligibility\Entities\SupplementalPatientData;
use CircleLinkHealth\Eligibility\NBISupplementaryDataNotFound;
use Illuminate\Support\Facades\Notification;

class ReplaceFieldsFromSupplementaryData extends BaseCcdaImportHook
{
    const IMPORTING_LISTENER_NAME = 'import_from_supplemental_patient_data';

    const NBI_PRACTICE_NAME = 'bethcare-newark-beth-israel';

    const RECEIVES_NBI_EXCEPTIONS_NOTIFICATIONS = 'receives_nbi_exceptions_notifications';

    public function run()
    {
        if (self::NBI_PRACTICE_NAME != $this->patient->primaryPractice->name) {
            return $this->payload;
        }

        $dataFromPractice = SupplementalPatientData::where('first_name', 'like', "{$this->patient->first_name}%")
            ->where('last_name', $this->patient->last_name)
            ->where('dob', $this->patient->patientInfo->birth_date)
            ->where(
                'practice_id',
                Practice::whereName(self::NBI_PRACTICE_NAME)->value('id')
            )
            ->first();

        if ( ! $dataFromPractice) {
            sendNbiPatientMrnWarning($this->patient->id);

            $recipients = AppConfig::pull(self::RECEIVES_NBI_EXCEPTIONS_NOTIFICATIONS, []);

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
}
