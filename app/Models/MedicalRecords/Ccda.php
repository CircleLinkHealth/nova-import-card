<?php namespace App\Models\MedicalRecords;

use App\Contracts\Importer\MedicalRecord\MedicalRecord;
use App\Contracts\Importer\MedicalRecord\MedicalRecordLogger;
use App\Entities\CcdaRequest;
use App\Importer\Loggers\Ccda\CcdaSectionsLogger;
use App\Importer\Models\ItemLogs\DocumentLog;
use App\Importer\Section\Importers\Allergies;
use App\Importer\Section\Importers\Demographics;
use App\Importer\Section\Importers\Insurance;
use App\Importer\Section\Importers\Medications;
use App\Importer\Section\Importers\Problems;
use App\Traits\MedicalRecordItemLoggerRelationships;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class Ccda extends Model implements MedicalRecord, Transformable
{

    use MedicalRecordItemLoggerRelationships,
        TransformableTrait,
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
     * @var
     */
    protected $billingProvider;

    protected $location;

    protected $practice;

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
     * Handles importing a MedicalRecord for QA.
     *
     * @return ImportedMedicalRecord
     *
     */
    public function import()
    {
        $this->createLogs()
            ->createImportedMedicalRecord()
            ->predictPractice()
            ->predictLocation()
            ->predictBillingProvider()
            ->importAllergies()
            ->importDemographics()
            ->importDocument()
            ->importInsurance()
            ->importMedications()
            ->importProblems()
            ->importProviders();
    }

    /**
     * Log the data into MedicalRecordSectionLogs, so that they can be fed to the Importer
     *
     * @return MedicalRecord
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
     * @return MedicalRecord
     */
    public function createImportedMedicalRecord() : MedicalRecord
    {
        $this->importedMedicalRecord = ImportedMedicalRecord::create([
            'medical_record_type' => self::class,
            'medical_record_id'   => $this->id,
            'billing_provider_id' => null,
            'location_id'         => null,
            'practice_id'         => null,
        ]);

        return $this;
    }

    /**
     * Import Allergies for QA
     *
     * @return MedicalRecord
     */
    public function importAllergies() : MedicalRecord
    {
        $importer = new Allergies();
        $importer->import($this->id, self::class, $this->importedMedicalRecord);

        return $this;
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
     * Import Demographics for QA
     *
     * @return \App\Contracts\Importer\MedicalRecord\MedicalRecord
     */
    public function importDemographics() : MedicalRecord
    {
        $importer = new Demographics();
        $importer->import($this->id, self::class, $this->importedMedicalRecord);

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
        $importer = new Medications();
        $importer->import($this->id, self::class, $this->importedMedicalRecord);

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
        $importer->import($this->id, self::class, $this->importedMedicalRecord);

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

    /**
     * Import Insurance Policies for QA
     *
     * @return MedicalRecord
     */
    public function importInsurance() : MedicalRecord
    {
        $importer = new Insurance();
        $importer->import($this->id, self::class, $this->importedMedicalRecord);

        return $this;
    }

    /**
     * Predict which Practice should be attached to this MedicalRecord.
     *
     * @return MedicalRecord
     */
    public function predictPractice() : MedicalRecord
    {
        //historic custodian lookup
        $custodianLookup = DocumentLog::where('custodian', '=', 'athenahealth')
            ->whereNotNull('practice_id')
            ->groupBy('practice_id')
            ->get(['practice_id'])
            ->keyBy('practice_id')
            ->keys();


        return $this;
    }

    /**
     * Predict which Location should be attached to this MedicalRecord.
     *
     * @return MedicalRecord
     */
    public function predictLocation() : MedicalRecord
    {
        //historic custodian lookup
        $custodianLookup = DocumentLog::where('custodian', '=', $this->document->custodian)
            ->whereNotNull('location_id')
            ->groupBy('location_id')
            ->get(['location_id']);


        return $this;
    }

    /**
     * Predict which BillingProvider should be attached to this MedicalRecord.
     *
     * @return MedicalRecord
     */
    public function predictBillingProvider() : MedicalRecord
    {
        // TODO: Implement predictBillingProvider() method.
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBillingProvider()
    {
        return $this->billingProvider;
    }

    /**
     * @param mixed $billingProvider
     *
     * @return Ccda
     */
    public function setBillingProvider($billingProvider)
    {
        $this->billingProvider = $billingProvider;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param mixed $location
     *
     * @return Ccda
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPractice()
    {
        return $this->practice;
    }

    /**
     * @param mixed $practice
     *
     * @return Ccda
     */
    public function setPractice($practice)
    {
        $this->practice = $practice;

        return $this;
    }
}
