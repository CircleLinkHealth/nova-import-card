<?php namespace App\Models\MedicalRecords;


use App\Contracts\Importer\MedicalRecord\MedicalRecordLogger;
use App\Entities\CcdaRequest;
use App\Importer\Loggers\Ccda\CcdaSectionsLogger;
use App\Importer\MedicalRecordEloquent;
use App\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class Ccda extends MedicalRecordEloquent implements Transformable
{
    use TransformableTrait,
        SoftDeletes;

    //define sources here
    const ATHENA_API = 'athena_api';
    const API = 'api';
    const EMR_DIRECT = 'emr_direct';
    const IMPORTER = 'importer';

    const EMAIL_DOMAIN_TO_VENDOR_MAP = [
        //Carolina Medical Associates
        '@direct.novanthealth.org'        => 10,
        '@test.directproject.net'         => 14,
        '@direct.welltrackone.com'        => 14,
        '@treatrelease.direct.aprima.com' => 1,
    ];

    protected $fillable = [
        'user_id',
        'patient_id',
        'vendor_id',
        'source',
        'imported',
        'xml',
        'json',
    ];

    /**
     * This is the patient that owns this CCDA.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id', 'id');
    }

    public function qaSummary()
    {
        return $this->hasOne(ImportedMedicalRecord::class);
    }

    public function ccdaRequest()
    {
        return $this->hasOne(CcdaRequest::class);
    }

    public function importedMedicalRecord()
    {
        return ImportedMedicalRecord::where('medical_record_type', '=', Ccda::class)
            ->where('medical_record_id', '=', $this->id)
            ->first();
    }


    /**
     * Get the Logger
     *
     * @return MedicalRecordLogger
     */
    public function getLogger() : MedicalRecordLogger
    {
        return new CcdaSectionsLogger($this);
    }

    /**
     * Get the User to whom this record belongs to, if one exists.
     *
     * @return User
     */
    public function getPatient() : User
    {
        return $this->patient;
    }

    /**
     * @return mixed
     */
    public function getBillingProvider()
    {
        return $this->billingProviderPrediction;
    }

    /**
     * @param mixed $billingProvider
     *
     * @return Ccda
     */
    public function setBillingProvider($billingProvider)
    {
        $this->billingProviderPrediction = $billingProvider;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLocation()
    {
        return $this->locationPrediction;
    }

    /**
     * @param mixed $location
     *
     * @return Ccda
     */
    public function setLocation($location)
    {
        $this->locationPrediction = $location;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPractice()
    {
        return $this->practicePrediction;
    }

    /**
     * @param mixed $practice
     *
     * @return Ccda
     */
    public function setPractice($practice)
    {
        $this->practicePrediction = $practice;

        return $this;
    }
}
