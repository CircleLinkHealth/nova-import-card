<?php namespace App\Models\CCD;

use App\CLH\CCD\ItemLogger\ModelLogRelationship;
use App\CLH\Contracts\CCD\HealthRecordSectionLog;
use App\Contracts\Importer\HealthRecord\HealthRecord;
use App\Contracts\Importer\HealthRecord\HealthRecordLogger;
use App\Contracts\Importer\ImportedHealthRecord\ImportedHealthRecord;
use App\Entities\CcdaRequest;
use App\Importer\Loggers\CcdaSectionsLogger;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class Ccda extends Model implements HealthRecord, Transformable
{

    use ModelLogRelationship, TransformableTrait;

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
        return $this->hasOne(QAImportSummary::class);
    }

    public function ccdaRequest()
    {
        return $this->hasOne(CcdaRequest::class);
    }

    /**
     * Handles importing a HealthRecord for QA.
     *
     * @return ImportedHealthRecord
     *
     */
    public function import() : ImportedHealthRecord
    {
        // TODO: Implement import() method.
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
     * Import Allergies for QA
     *
     * @return HealthRecord
     */
    public function importAllergies() : HealthRecord
    {
        // TODO: Implement importAllergies() method.
    }

    /**
     * Import Demographics for QA
     *
     * @return HealthRecord
     */
    public function importDemographics() : HealthRecord
    {
        // TODO: Implement importDemographics() method.
    }

    /**
     * Import Document for QA
     *
     * @return HealthRecord
     */
    public function importDocument() : HealthRecord
    {
        // TODO: Implement importDocument() method.
    }

    /**
     * Import Medications for QA
     *
     * @return HealthRecord
     */
    public function importMedications() : HealthRecord
    {
        // TODO: Implement importMedications() method.
    }

    /**
     * Import Problems for QA
     *
     * @return HealthRecord
     */
    public function importProblems() : HealthRecord
    {
        // TODO: Implement importProblems() method.
    }

    /**
     * Import Providers for QA
     *
     * @return HealthRecord
     */
    public function importProviders() : HealthRecord
    {
        // TODO: Implement importProviders() method.
    }

    /**
     * Log the data into HealthRecordSectionLogs, so that they can be fed to the Importer
     *
     * @return HealthRecordSectionLog|HealthRecord
     */
    public function createLogs() : HealthRecord
    {
        $this->getLogger()->logAllSections();
    }

    /**
     * Get the Logger
     *
     * @return HealthRecordLogger
     */
    public function getLogger() : HealthRecordLogger
    {
        return new CcdaSectionsLogger($this);
    }
}
