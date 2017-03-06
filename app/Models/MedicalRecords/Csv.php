<?php

namespace App\Models\MedicalRecords;

use App\Contracts\Importer\MedicalRecord\MedicalRecord;
use App\Contracts\Importer\MedicalRecord\MedicalRecordLogger;
use App\Importer\MedicalRecordEloquent;
use App\User;

class Csv extends MedicalRecordEloquent
{
    /**
     * Table Name
     *
     * @var string
     */
    protected $table = 'csv_medical_records';

    protected $fillable = [
        'practice_id',
        'location_id',
        'billing_provider_id',
        'uploaded_by',
        'patient_id',
        'first_name',
        'last_name',
        'dob',
        'provider_name',
        'phone',
    ];

    /**
     * Get the Transformer
     *
     * @return MedicalRecordLogger
     */
    public function getLogger() : MedicalRecordLogger
    {

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
}
