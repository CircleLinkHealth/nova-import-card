<?php

namespace App\Models\MedicalRecords;

use App\Contracts\Importer\MedicalRecord\MedicalRecordLogger;
use App\Importer\Loggers\Csv\PhoenixHeartSectionsLogger;
use App\Importer\Loggers\Csv\TabularMedicalRecordSectionsLogger;
use App\Importer\MedicalRecordEloquent;
use App\Practice;
use App\User;

class TabularMedicalRecord extends MedicalRecordEloquent
{
    protected $dates = [
        'dob',
        'consent_date',
    ];

    protected $fillable = [
        'practice_id',
        'location_id',
        'billing_provider_id',

        'uploaded_by',

        'patient_id',

        'mrn',
        'first_name',
        'last_name',
        'dob',

        'allergies_string',
        'medications_string',
        'problems_string',

        'gender',
        'language',
        'consent_date',

        'provider_name',

        'primary_phone',
        'cell_phone',
        'home_phone',
        'work_phone',
        'email',

        'address',
        'address2',
        'city',
        'state',
        'zip',

        'primary_insurance',
        'secondary_insurance',
        'tertiary_insurance',

        'preferred_call_times',
        'preferred_call_days',
    ];

    /**
     * Get the Transformer
     *
     * @return MedicalRecordLogger
     */
    public function getLogger(): MedicalRecordLogger
    {
        $phoenixHeart = Practice::whereDisplayName('Phoenix Heart')->first();

        if ($this->practice_id == $phoenixHeart->id) {
            return new PhoenixHeartSectionsLogger($this, $phoenixHeart);
        }

        return new TabularMedicalRecordSectionsLogger($this);
    }

    /**
     * Get the User to whom this record belongs to, if one exists.
     *
     * @return User
     */
    public function getPatient(): User
    {
        // TODO: Implement getPatient() method.
    }

    public function getDocumentCustodian(): string
    {
        return '';
    }
}
