<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

use CircleLinkHealth\Eligibility\Adapters\CcdaToEligibilityJobAdapter;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\MedicalRecordLogger;
use App\DirectMailMessage;
use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use App\Entities\CcdaRequest;
use CircleLinkHealth\Core\Exceptions\InvalidCcdaException;
use App\Importer\Loggers\Ccda\CcdaSectionsLogger;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\MedicalRecordEloquent;
use CircleLinkHealth\Eligibility\Entities\TargetPatient;
use App\Traits\Relationships\BelongsToPatientUser;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Events\CcdaImported;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

/**
 * CircleLinkHealth\SharedModels\Entities\Ccda.
 *
 * @property int $id
 * @property \Carbon\Carbon|null $date
 * @property string|null $mrn
 * @property string|null $referring_provider_name
 * @property int|null $location_id
 * @property int|null $practice_id
 * @property int|null $billing_provider_id
 * @property int|null $user_id
 * @property int|null $patient_id
 * @property int $vendor_id
 * @property string $source
 * @property int $imported
 * @property string $xml
 * @property string|null $json
 * @property string|null $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string|null $deleted_at
 * @property \CircleLinkHealth\SharedModels\Entities\AllergyLog[]|\Illuminate\Database\Eloquent\Collection $allergies
 * @property \App\Entities\CcdaRequest $ccdaRequest
 * @property \CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\DemographicsLog[]|\Illuminate\Database\Eloquent\Collection
 *     $demographics
 * @property \CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\DemographicsImport[]|\Illuminate\Database\Eloquent\Collection
 *     $demographicsImports
 * @property \CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\DocumentLog[]|\Illuminate\Database\Eloquent\Collection
 *     $document
 * @property \CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\MedicationLog[]|\Illuminate\Database\Eloquent\Collection
 *     $medications
 * @property \CircleLinkHealth\Customer\Entities\User|null $patient
 * @property \CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProblemLog[]|\Illuminate\Database\Eloquent\Collection
 *     $problems
 * @property \CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProviderLog[]|\Illuminate\Database\Eloquent\Collection
 *     $providers
 * @property \CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord $qaSummary
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MedicalRecords\Ccda onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereBillingProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereImported($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereMrn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda wherePracticeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Ccda
 *     whereReferringProviderName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereVendorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereXml($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MedicalRecords\Ccda withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MedicalRecords\Ccda withoutTrashed()
 * @mixin \Eloquent
 * @property int|null $direct_mail_message_id
 * @property int|null $batch_id
 * @property \App\DirectMailMessage $directMessage
 * @property \App\Media[]|\Illuminate\Database\Eloquent\Collection $media
 * @property \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Revisionable\Entities\Revision[]
 *     $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda exclude($value = [])
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda
 *     whereDirectMailMessageId($value)
 * @property \CircleLinkHealth\Eligibility\Entities\EligibilityBatch|null $batch
 * @property \CircleLinkHealth\Customer\Entities\Practice|null $practice
 * @property int|null $allergies_count
 * @property int|null $demographics_count
 * @property int|null $demographics_imports_count
 * @property int|null $document_count
 * @property int|null $media_count
 * @property int|null $medications_count
 * @property int|null $problems_count
 * @property int|null $providers_count
 * @property int|null $revision_history_count
 * @property \CircleLinkHealth\Eligibility\Entities\TargetPatient $targetPatient
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Ccda hasUPG0506Media()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Ccda
 *     hasUPG0506PdfCareplanMedia()
 */
class Ccda extends MedicalRecordEloquent implements HasMedia
{
    const CCD_MEDIA_COLLECTION_NAME = 'ccd';

    use BelongsToPatientUser;
    use HasMediaTrait;
    use SoftDeletes;
    const API = 'api';

    //define sources here
    const ATHENA_API = 'athena_api';

    const EMR_DIRECT   = 'emr_direct';
    const GOOGLE_DRIVE = 'google_drive';
    const IMPORTER     = 'importer';
    const IMPORTER_AWV = 'importer_awv';
    const SFTP_DROPBOX = 'sftp_dropbox';
    const UPLOADED     = 'uploaded';

    protected $attributes = [
        'imported' => false,
    ];

    protected $dates = [
        'date',
    ];

    protected $fillable = [
        'direct_mail_message_id',
        'batch_id',
        'date',
        'mrn',
        'referring_provider_name',
        'location_id',
        'practice_id',
        'billing_provider_id',
        'user_id',
        'patient_id',
        'source',
        'imported',
        'json',
        'xml',
        'status',
    ];

    public function batch()
    {
        return $this->belongsTo(EligibilityBatch::class);
    }

    public function bluebuttonJson()
    {
        if ($this->json) {
            return json_decode($this->json);
        }

        if ( ! $this->id || ! $this->hasMedia(self::CCD_MEDIA_COLLECTION_NAME)) {
            return false;
        }

        if ( ! $this->json) {
            if ($parsedJson = $this->getParsedJson()) {
                $this->json = $parsedJson;
                $this->save();
            } else {
                $this->parseToJson();
            }
        }

        return json_decode($this->json);
    }

    public function ccdaRequest()
    {
        return $this->hasOne(CcdaRequest::class);
    }

    /**
     * Store Ccda and store xml as Media.
     *
     * @param array $attributes
     *
     * @return Ccda|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|\CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\MedicalRecordEloquent
     */
    public static function create($attributes = [])
    {
        if ( ! array_key_exists('xml', $attributes)) {
            return static::query()->create($attributes);
        }

        $xml = $attributes['xml'];
        unset($attributes['xml']);

        $ccda = static::query()->create($attributes);

        $filename = null;
        if (array_key_exists('filename', $attributes)) {
            $filename = $attributes['filename'];
            unset($attributes['filename']);
        }

        if ( ! $filename) {
            $filename = "ccda-{$ccda->id}.xml";
        }

        \Storage::disk('storage')->put($filename, $xml);
        $ccda->addMedia(storage_path($filename))->toMediaCollection(self::CCD_MEDIA_COLLECTION_NAME);

        return $ccda;
    }

    /**
     * @throws \Exception
     */
    public function createEligibilityJobFromMedicalRecord(): ?EligibilityJob
    {
        $adapter = new CcdaToEligibilityJobAdapter($this, $this->practice, $this->batch);

        return $adapter->adaptToEligibilityJob();
    }

    public function directMessage()
    {
        return $this->belongsTo(DirectMailMessage::class, 'direct_mail_message_id');
    }

    public function getDocumentCustodian(): string
    {
        if ($this->document->first()) {
            return $this->document->first()->custodian;
        }

        return '';
    }

    /**
     * Get the Logger.
     */
    public function getLogger(): MedicalRecordLogger
    {
        return new CcdaSectionsLogger($this);
    }

    /**
     * Get the User to whom this record belongs to, if one exists.
     */
    public function getPatient(): ?User
    {
        return $this->patient;
    }

    public function getReferringProviderName()
    {
        return $this->referring_provider_name;
    }

    public function importedMedicalRecord(): ?ImportedMedicalRecord
    {
        return ImportedMedicalRecord::where('medical_record_type', '=', Ccda::class)
                                    ->where('medical_record_id', '=', $this->id)
                                    ->first();
    }

    public function practice()
    {
        return $this->belongsTo(Practice::class);
    }

    public function qaSummary()
    {
        return $this->hasOne(ImportedMedicalRecord::class);
    }

    public function scopeExclude($query, $value = [])
    {
        $defaultColumns = ['id', 'created_at', 'updated_at'];

        return $query->select(array_diff(array_merge($defaultColumns, $this->fillable), (array) $value));
    }

    public function scopeHasUPG0506Media($query)
    {
        return $query->whereHas(
            'media',
            function ($q) {
                $q->where('custom_properties->is_ccda', 'true')->where('custom_properties->is_upg0506', 'true');
            }
        );
    }

    public function getUPG0506PdfCareplanMedia()
    {
        return \DB::table('media')
                  ->where('custom_properties->is_pdf', 'true')
                  ->where('custom_properties->is_upg0506', 'true')
                  ->where('custom_properties->care_plan->demographics->mrn_number', (string) $this->mrn)
                  ->first();
    }

    public function storeCcd($xml)
    {
        if ( ! $this->id) {
            throw new \Exception('CCD does not have an id.');
        }

        \Storage::disk('storage')->put("ccda-{$this->id}.xml", $xml);
        $this->addMedia(storage_path("ccda-{$this->id}.xml"))->toMediaCollection(self::CCD_MEDIA_COLLECTION_NAME);

        return $this;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function targetPatient()
    {
        return $this->hasOne(TargetPatient::class);
    }

    protected function parseToJson()
    {
        $xmlMedia = $this->getMedia(self::CCD_MEDIA_COLLECTION_NAME)->first();
        $xml      = $xmlMedia->getFile();
        if (( ! is_string($xml)) || (strlen($xml) < 1) || (false === stripos($xml, '<ClinicalDocument'))) {
            $this->json   = null;
            $this->status = 'invalid';
            $this->save();
            throw new InvalidCcdaException($this->id);
        }

        $xmlPath = storage_path("ccdas/import/media_{$xmlMedia->id}.xml");
        file_put_contents($xmlPath, $xml);

        $jsonPath = storage_path("ccdas/import/ccda_{$this->id}.json");

        Artisan::call(
            'ccd:parse',
            [
                'ccdaId'     => $this->id,
                'inputPath'  => $xmlPath,
                'outputPath' => $jsonPath,
            ]
        );

        if (file_exists($xmlPath)) {
            \Storage::delete($xmlPath);
        }

        if (file_exists($jsonPath)) {
            $this->json = file_get_contents($jsonPath);
            $this->save();
            \Storage::delete($jsonPath);

            return;
        }

        $json = $this->getParsedJson();

        $decoded = json_decode($json);

        $this->json = $json;
        $this->mrn  = optional($decoded->demographics)->mrn_number;
        $this->save();
    }

    /**
     * Gets the parsed json from the parser's table, if it was already parsed.
     *
     * @return string|null
     */
    private function getParsedJson()
    {
        return optional(DB::table(config('ccda-parser.db_table'))->where('ccda_id', '=', $this->id)->first())->result;
    }

    /**
     * Checks the procedues section of the CCDA for codes
     *
     * @param string $code
     *
     * @return bool
     */
    public function hasProcedureCode(string $code): bool
    {
        return collect(
            $this->bluebuttonJson()->procedures
        )->pluck('code')->contains($code);
    }

    public function import()
    {
        $imported = parent::import();

        if ($imported instanceof ImportedMedicalRecord) {
            $this->imported = true;
            $this->status = Ccda::QA;
            $this->save();

            event(new CcdaImported($this->getId()));
        }

        return $imported;
    }
}
