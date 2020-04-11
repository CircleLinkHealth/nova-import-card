<?php


namespace CircleLinkHealth\Eligibility\CcdaImporter\Tasks;


use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\CcdaImporter\BaseCcdaImportTask;
use CircleLinkHealth\Eligibility\Entities\SupplementalPatientData;
use CircleLinkHealth\Eligibility\NBISupplementaryDataNotFound;
use Illuminate\Support\Facades\Notification;

class ReplaceFieldsFromSupplementaryData
{
    const IMPORTING_LISTENER_NAME = 'import.from.supplemental.patient.data';
    /**
     * @var User
     */
    protected $patient;
    
    /**
     * ReplaceFieldsFromSupplementaryData constructor.
     *
     * @param User $patient
     */
    public function __construct(User $patient)
    {
        $this->patient = $patient;
    }
    
    const NBI_PRACTICE_NAME = 'bethcare-newark-beth-israel';
    
    const RECEIVES_NBI_EXCEPTIONS_NOTIFICATIONS = 'receives_nbi_exceptions_notifications';
    
    public function run()
    {
        if (self::NBI_PRACTICE_NAME != $this->patient->primaryPractice->name) {
            return null;
        }
    
        $dataFromPractice = SupplementalPatientData::where('first_name', 'like', "{$this->patient->first_name}%")
                                                   ->where('last_name', $this->patient->last_name)
                                                   ->where('dob', $this->patient->patientInfo->dob)
                                                   ->where('practice_id', Practice::whereName(self::NBI_PRACTICE_NAME)->value('id'))
                                                   ->first();
    
        if ( ! $dataFromPractice) {
            sendNbiPatientMrnWarning($this->patient->id);
        
            $recipients = AppConfig::where('config_key', '=', self::RECEIVES_NBI_EXCEPTIONS_NOTIFICATIONS)->get();
        
            foreach ($recipients as $recipient) {
                Notification::route('mail', $recipient->config_value)
                            ->notify(new NBISupplementaryDataNotFound($this->patient));
            }
        }
    
        if (optional($dataFromPractice)->mrn) {
            return $dataFromPractice->mrn;
        }
        
        return null;
    }
}