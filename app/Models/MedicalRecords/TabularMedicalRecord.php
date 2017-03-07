<?php

namespace App\Models\MedicalRecords;

use App\Contracts\Importer\MedicalRecord\MedicalRecord;
use App\Contracts\Importer\MedicalRecord\MedicalRecordLogger;
use App\Importer\Loggers\Csv\TabularMedicalRecordSectionsLogger;
use App\Importer\MedicalRecordEloquent;
use App\User;

class TabularMedicalRecord extends MedicalRecordEloquent
{
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

        'gender',
        'language',

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
    ];

    /**
     * Get the Transformer
     *
     * @return MedicalRecordLogger
     */
    public function getLogger() : MedicalRecordLogger
    {
        return new TabularMedicalRecordSectionsLogger($this);
    }

    /**
     * Get the User to whom this record belongs to, if one exists.
     *
     * @return User
     */
    public function getPatient() : User
    {
        // TODO: Implement getPatient() method.
    }

    /**
     * @return mixed
     */
    public function getBillingProviderIdPrediction()
    {
        // TODO: Implement getBillingProviderIdPrediction() method.
    }

    /**
     * @param mixed $billingProvider
     *
     * @return MedicalRecord
     */
    public function setBillingProviderIdPrediction($billingProvider) : MedicalRecord
    {
        // TODO: Implement setBillingProviderIdPrediction() method.
    }

    /**
     * @return mixed
     */
    public function getLocationIdPrediction()
    {
        // TODO: Implement getLocationIdPrediction() method.
    }

    /**
     * @param mixed $location
     *
     * @return MedicalRecord
     */
    public function setLocationIdPrediction($location) : MedicalRecord
    {
        // TODO: Implement setLocationIdPrediction() method.
    }

    /**
     * @param mixed $practice
     *
     * @return MedicalRecord
     */
    public function setPracticeIdPrediction($practice) : MedicalRecord
    {
        // TODO: Implement setPracticeIdPrediction() method.
    }


    public function getDocumentCustodian() : string
    {
        return '';
    }
}
