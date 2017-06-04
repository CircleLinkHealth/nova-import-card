<?php namespace App\Models\MedicalRecords;


use App\Contracts\Importer\MedicalRecord\MedicalRecord;
use App\Contracts\Importer\MedicalRecord\MedicalRecordLogger;
use App\Entities\CcdaRequest;
use App\Importer\Loggers\Ccda\CcdaSectionsLogger;
use App\Importer\MedicalRecordEloquent;
use App\Traits\Relationships\BelongsToPatientUser;
use App\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class Ccda extends MedicalRecordEloquent implements Transformable
{
    use BelongsToPatientUser,
        TransformableTrait,
        SoftDeletes;

    //define sources here
    const ATHENA_API = 'athena_api';
    const API = 'api';
    const EMR_DIRECT = 'emr_direct';
    const IMPORTER = 'importer';
    const SFTP_DROPBOX = 'sftp_dropbox';

    const EMAIL_DOMAIN_TO_VENDOR_MAP = [
        //Carolina Medical Associates
        '@direct.novanthealth.org'        => 10,
        '@test.directproject.net'         => 14,
        '@direct.welltrackone.com'        => 14,
        '@treatrelease.direct.aprima.com' => 1,
    ];

    protected $dates = [
        'date'
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
     * @return string
     */
    public function getDocumentCustodian() : string
    {
        if ($this->document->first()) {
            return $this->document->first()->custodian;
        }

        return '';
    }
}
