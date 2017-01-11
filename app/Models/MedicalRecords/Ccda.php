<?php namespace App\Models\MedicalRecords;

use App\Contracts\Importer\ImportedMedicalRecord\ImportedMedicalRecord;
use App\Contracts\Importer\MedicalRecord\MedicalRecord;
use App\Contracts\Importer\MedicalRecord\MedicalRecordLogger;
use App\Contracts\Importer\MedicalRecord\Section\ItemLog;
use App\Entities\CcdaRequest;
use App\Importer\Loggers\Ccda\CcdaSectionsLogger;
use App\Importer\Section\Importers\Allergies;
use App\Importer\Section\Importers\Problems;
use App\Models\CCD\QAImportSummary;
use App\Traits\MedicalRecordItemLoggerRelationships;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class Ccda extends Model implements MedicalRecord, Transformable
{

    use MedicalRecordItemLoggerRelationships, TransformableTrait;

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
     * Handles importing a MedicalRecord for QA.
     *
     * @return ImportedMedicalRecord
     *
     */
    public function import()
    {
        $this->createLogs()
            ->importAllergies()
            ->importDemographics()
            ->importDocument()
            ->importMedications()
            ->importProblems()
            ->importProviders();
    }

    /**
     * Log the data into MedicalRecordSectionLogs, so that they can be fed to the Importer
     *
     * @return ItemLog|\App\Contracts\Importer\MedicalRecord\MedicalRecord
     */
    public function createLogs() : MedicalRecord
    {
        $this->getLogger()->logAllSections();

        return $this;
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
     * Import Allergies for QA
     *
     * @return MedicalRecord
     */
    public function importAllergies() : MedicalRecord
    {
        $importer = new Allergies();
        $importer->import($this->id, self::class);

        return $this;
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
     * Import Demographics for QA
     *
     * @return \App\Contracts\Importer\MedicalRecord\MedicalRecord
     */
    public function importDemographics() : MedicalRecord
    {
        return $this;

    }

    /**
     * Import Document for QA
     *
     * @return \App\Contracts\Importer\MedicalRecord\MedicalRecord
     */
    public function importDocument() : MedicalRecord
    {
        return $this;

    }

    /**
     * Import Medications for QA
     *
     * @return \App\Contracts\Importer\MedicalRecord\MedicalRecord
     */
    public function importMedications() : MedicalRecord
    {
        return $this;

    }

    /**
     * Import Problems for QA
     *
     * @return \App\Contracts\Importer\MedicalRecord\MedicalRecord
     */
    public function importProblems() : MedicalRecord
    {
        $importer = new Problems();
        $importer->import($this->id, self::class);

        return $this;
    }

    /**
     * Import Providers for QA
     *
     * @return \App\Contracts\Importer\MedicalRecord\MedicalRecord
     */
    public function importProviders() : MedicalRecord
    {
        return $this;

    }
}
