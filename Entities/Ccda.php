<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

use App\DirectMailMessage;
use App\Entities\CcdaRequest;
use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Core\Exceptions\InvalidCcdaException;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Adapters\CcdaToEligibilityJobAdapter;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportPatientInfo;
use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\Entities\TargetPatient;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\MedicalRecord;
use CircleLinkHealth\SharedModels\Traits\BelongsToPatientUser;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Modules\Eligibility\CcdaImporter\CcdaImporterWrapper;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

/**
 * CircleLinkHealth\SharedModels\Entities\Ccda.
 *
 * @property int                                                                                         $id
 * @property int|null                                                                                    $direct_mail_message_id
 * @property int|null                                                                                    $batch_id
 * @property \Illuminate\Support\Carbon|null                                                             $date
 * @property string|null                                                                                 $mrn
 * @property string|null                                                                                 $referring_provider_name
 * @property int|null                                                                                    $location_id
 * @property int|null                                                                                    $practice_id
 * @property int|null                                                                                    $billing_provider_id
 * @property int|null                                                                                    $user_id
 * @property int|null                                                                                    $patient_id
 * @property string                                                                                      $source
 * @property int                                                                                         $imported
 * @property mixed|null                                                                                  $json
 * @property string|null                                                                                 $status
 * @property array|null                                                                                  $validation_checks
 * @property \Illuminate\Support\Carbon                                                                  $created_at
 * @property \Illuminate\Support\Carbon                                                                  $updated_at
 * @property \Illuminate\Support\Carbon|null                                                             $deleted_at
 * @property \CircleLinkHealth\Eligibility\Entities\EligibilityBatch|null                                $batch
 * @property \App\Entities\CcdaRequest                                                                   $ccdaRequest
 * @property \App\DirectMailMessage|null                                                                 $directMessage
 * @property \CircleLinkHealth\Customer\Entities\Location|null                                           $location
 * @property \CircleLinkHealth\Customer\Entities\Media[]|\Illuminate\Database\Eloquent\Collection        $media
 * @property int|null                                                                                    $media_count
 * @property \CircleLinkHealth\Customer\Entities\User|null                                               $patient
 * @property \CircleLinkHealth\Customer\Entities\Practice|null                                           $practice
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @property int|null                                                                                    $revision_history_count
 * @property \CircleLinkHealth\Eligibility\Entities\TargetPatient                                        $targetPatient
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Ccda exclude($value = [])
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Ccda hasUPG0506Media()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Ccda hasUPG0506PdfCareplanMedia()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Ccda newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Ccda newQuery()
 * @method static \Illuminate\Database\Query\Builder|\CircleLinkHealth\SharedModels\Entities\Ccda onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Ccda query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Ccda whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Ccda whereBillingProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Ccda whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Ccda whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Ccda whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Ccda whereDirectMailMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Ccda whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Ccda whereImported($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Ccda whereJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Ccda whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Ccda whereMrn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Ccda wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Ccda wherePracticeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Ccda whereReferringProviderName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Ccda whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Ccda whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Ccda whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Ccda whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Ccda whereValidationChecks($value)
 * @method static \Illuminate\Database\Query\Builder|\CircleLinkHealth\SharedModels\Entities\Ccda withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\CircleLinkHealth\SharedModels\Entities\Ccda withoutTrashed()
 * @mixin \Eloquent
 *
 * @property string|null                                   $patient_first_name
 * @property string|null                                   $patient_last_name
 * @property string|null                                   $patient_mrn
 * @property string|null                                   $patient_dob
 * @property string|null                                   $patient_email
 * @property \CircleLinkHealth\Customer\Entities\User|null $billingProvider
 */
class Ccda extends BaseModel implements HasMedia, MedicalRecord
{
    use BelongsToPatientUser;
    use HasMediaTrait;
    use SoftDeletes;
    const API = 'api';

    //define sources here
    const ATHENA_API = 'athena_api';

    const CCD_MEDIA_COLLECTION_NAME = 'ccd';

    const EMR_DIRECT   = 'emr_direct';
    const GOOGLE_DRIVE = 'google_drive';
    const IMPORTER     = 'importer';
    const IMPORTER_AWV = 'importer_awv';
    const SFTP_DROPBOX = 'sftp_dropbox';
    const UPLOADED     = 'uploaded';
    /**
     * Duplicate patient user ID.
     *
     * @var int
     */
    public $duplicate_id;

    protected $attributes = [
        'imported' => false,
    ];

    protected $casts = [
        'validation_checks' => 'collection',
    ];

    protected $dates = [
        'date',
    ];

    protected $dontKeepRevisionOf = ['json'];

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
        'duplicate_id',
        'validation_checks',
    ];

    /**
     * A collection.
     *
     * @var Collection
     */
    protected $insurances;

    private $decodedJson;

    public function batch()
    {
        return $this->belongsTo(EligibilityBatch::class);
    }

    public function billingProvider()
    {
        return $this->belongsTo(User::class, 'billing_provider_id');
    }

    public function bluebuttonJson($purgeDecodedCcda = false)
    {
        if (true === $purgeDecodedCcda) {
            $this->decodedJson = null;
        }

        if ( ! empty($this->decodedJson)) {
            return $this->decodedJson;
        }

        if ($this->json) {
            $this->decodedJson = json_decode($this->json);

            return $this->decodedJson;
        }

        if ( ! $this->id || ! $this->hasMedia(self::CCD_MEDIA_COLLECTION_NAME)) {
            return false;
        }

        if ( ! $this->json) {
            if ($parsedJson = $this->getParsedJson()) {
                $this->json = $parsedJson;
                if ( ! $this->mrn) {
                    $this->patient_mrn;
                }
                $this->save();
            } else {
                $this->parseToJson();
            }
        }

        $this->decodedJson = json_decode($this->json);

        return $this->decodedJson;
    }

    public function ccdaRequest()
    {
        return $this->hasOne(CcdaRequest::class);
    }

    /**
     * @return int|mixed|null
     * @todo: duplicate of Importer/MedicalRecordEloquent.php @ raiseConcernsOrAutoQAApprove()
     */
    public function checkDuplicity()
    {
        $this->duplicate_id = null;

        $user = User::whereFirstName($this->patient_first_name)
            ->whereLastName($this->patient_last_name)
            ->whereHas(
                'patientInfo',
                function ($q) {
                    $q->where('birth_date', $this->patientDob());
                }
            )->when($this->practice_id, function ($q) {
                $q->where('program_id', $this->practice_id);
            })->when($this->patient_id, function ($q) {
                $q->where('id', '!=', $this->patient_id);
            })->first();

        if ($user) {
            $this->duplicate_id = $user->id;

            return $user->id;
        }

        $patient = Patient::whereHas(
            'user',
            function ($q) {
                $q->where('program_id', $this->practice_id);
            }
        )->whereMrnNumber($this->patient_mrn)->whereNotNull('mrn_number')
            ->when($this->patient_id, function ($q) {
                $q->where('user_id', '!=', $this->patient_id);
            })
            ->first();

        if ($patient) {
            $this->duplicate_id = $patient->user_id;

            return $patient->user_id;
        }

        return null;
    }

    /**
     * Store Ccda and store xml as Media.
     *
     * @param array $attributes
     *
     * @return Ccda|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
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

    /**
     * @return mixed
     */
    public function getBillingProviderId(): ?int
    {
        return $this->billing_provider_id;
    }

    public function getDocumentCustodian(): string
    {
        if ($this->document->first()) {
            return $this->document->first()->custodian;
        }

        return '';
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    /**
     * @return mixed
     */
    public function getLocationId(): ?int
    {
        return $this->location_id;
    }

    /**
     * Get the User to whom this record belongs to, if one exists.
     */
    public function getPatient(): ?User
    {
        return $this->patient;
    }

    /**
     * @return mixed
     */
    public function getPracticeId(): ?int
    {
        return $this->practice_id;
    }

    public function getReferringProviderName()
    {
        if ( ! empty($name = trim($this->referring_provider_name))) {
            return $name;
        }

        if ( ! empty($name = trim($this->ccdaAuthor()))) {
            return $name;
        }
        if ( ! empty($name = trim($this->ccdaDocumentationOf()))) {
            return $name;
        }
    }

    public function getType(): ?string
    {
        return get_class($this);
    }

    public function getUPG0506PdfCareplanMedia()
    {
        return \DB::table('media')
            ->where('custom_properties->is_pdf', 'true')
            ->where('custom_properties->is_upg0506', 'true')
            ->where('custom_properties->care_plan->demographics->mrn_number', (string) $this->mrn)
            ->first();
    }

    /**
     * Checks the procedues section of the CCDA for codes.
     */
    public function hasProcedureCode(string $code): bool
    {
        return collect(
            $this->bluebuttonJson()->procedures ?? []
        )->pluck('code')->contains($code);
    }

    /**
     * Handles importing a MedicalRecordForEligibilityCheck for QA.
     *
     * @return Ccda
     */
    public function import(Enrollee &$enrollee = null)
    {
        return with(new CcdaImporterWrapper($this, $enrollee))->import();
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function patientDob(): ?Carbon
    {
        return ImportPatientInfo::parseDOBDate($this->patient_dob ?? $this->bluebuttonJson()->demographics->dob ?? null);
    }

    public function practice()
    {
        return $this->belongsTo(Practice::class);
    }

    public function queryForOtherCcdasForTheSamePatient()
    {
        return Ccda::where('id', '!=', $this->id)
            ->where('practice_id', $this->practice_id)
            ->where('patient_mrn', $this->patient_mrn)
//            @Constantinos: Any suggestions on how to handle matching when a the patient's first name has a last name?
//
//            Example:
//                $this->patient_first_name === 'John, K'
//                ccdas.patient_first_name === 'John'
//                Can we make this match?
//
//                $this->patient_first_name === 'John K.'
//                ccdas.patient_first_name === 'John'
//                This too?
//
//            ->where('patient_first_name', $this->patient_first_name)
            ->where('patient_last_name', $this->patient_last_name)
            ->where('patient_dob', $this->patient_dob);
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
                $q->where('is_ccda', true)->where('is_upg0506', true);
            }
        );
    }

    public function scopeHasUPG0506PdfCareplanMedia($query)
    {
        return $query->whereExists(
            function ($query) {
                $query->select('id')
                    ->from('media')
                    ->where('is_pdf', true)->where(
                        'is_upg0506',
                        true
                    )->where('mrn', (string) $this->mrn);
            }
        );
    }

    /**
     * @param mixed $billingProviderId
     */
    public function setBillingProviderId($billingProviderId): MedicalRecord
    {
        $this->billing_provider_id = $billingProviderId;

        return $this;
    }

    /**
     * @param mixed $locationId
     */
    public function setLocationId(int $locationId): MedicalRecord
    {
        $this->location_id = $locationId;

        return $this;
    }

    /**
     * @param mixed $practiceId
     */
    public function setPracticeId($practiceId): MedicalRecord
    {
        $this->practice_id = $practiceId;

        return $this;
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
        $this->mrn  = $this->patient_mrn;
        $this->save();
    }

    private function ccdaAuthor()
    {
        $fName = $this->bluebuttonJson()->document->author->name->given[0] ?? '';
        $lName = $this->bluebuttonJson()->document->author->name->family ?? '';
        $name  = trim("$fName $lName");

        if (empty($name)) {
            return null;
        }

        return $name;
    }

    private function ccdaDocumentationOf()
    {
        if ( ! $doc = $this->bluebuttonJson()->document->documentation_of[0] ?? null) {
            return;
        }
        $fName = $doc->name->given[0] ?? '';
        $lName = $doc->name->family ?? '';
        $name  = trim("$fName $lName");

        if (empty($name)) {
            return null;
        }

        return $name;
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
}
