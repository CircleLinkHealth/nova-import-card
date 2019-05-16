<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Models\MedicalRecords;

use App\Contracts\Importer\MedicalRecord\MedicalRecordLogger;
use App\DirectMailMessage;
use App\Entities\CcdaRequest;
use App\Importer\Loggers\Ccda\CcdaSectionsLogger;
use App\Importer\MedicalRecordEloquent;
use App\Traits\Relationships\BelongsToPatientUser;
use CircleLinkHealth\Customer\Entities\User;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\SoftDeletes;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

/**
 * App\Models\MedicalRecords\Ccda.
 *
 * @property int                                                                                 $id
 * @property \Carbon\Carbon|null                                                                 $date
 * @property string|null                                                                         $mrn
 * @property string|null                                                                         $referring_provider_name
 * @property int|null                                                                            $location_id
 * @property int|null                                                                            $practice_id
 * @property int|null                                                                            $billing_provider_id
 * @property int|null                                                                            $user_id
 * @property int|null                                                                            $patient_id
 * @property int                                                                                 $vendor_id
 * @property string                                                                              $source
 * @property int                                                                                 $imported
 * @property string                                                                              $xml
 * @property string|null                                                                         $json
 * @property string|null                                                                         $status
 * @property \Carbon\Carbon                                                                      $created_at
 * @property \Carbon\Carbon                                                                      $updated_at
 * @property string|null                                                                         $deleted_at
 * @property \App\Importer\Models\ItemLogs\AllergyLog[]|\Illuminate\Database\Eloquent\Collection $allergies
 * @property \App\Entities\CcdaRequest                                                           $ccdaRequest
 * @property \App\Importer\Models\ItemLogs\DemographicsLog[]|\Illuminate\Database\Eloquent\Collection
 *     $demographics
 * @property \App\Importer\Models\ImportedItems\DemographicsImport[]|\Illuminate\Database\Eloquent\Collection
 *     $demographicsImports
 * @property \App\Importer\Models\ItemLogs\DocumentLog[]|\Illuminate\Database\Eloquent\Collection   $document
 * @property \App\Importer\Models\ItemLogs\MedicationLog[]|\Illuminate\Database\Eloquent\Collection $medications
 * @property \CircleLinkHealth\Customer\Entities\User|null                                          $patient
 * @property \App\Importer\Models\ItemLogs\ProblemLog[]|\Illuminate\Database\Eloquent\Collection    $problems
 * @property \App\Importer\Models\ItemLogs\ProviderLog[]|\Illuminate\Database\Eloquent\Collection   $providers
 * @property \App\Models\MedicalRecords\ImportedMedicalRecord                                       $qaSummary
 *
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
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda
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
 *
 * @property int|null                                                                       $direct_mail_message_id
 * @property int|null                                                                       $batch_id
 * @property \App\DirectMailMessage                                                         $directMessage
 * @property \App\Media[]|\Illuminate\Database\Eloquent\Collection                          $media
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda exclude($value = array())
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereDirectMailMessageId($value)
 */
class Ccda extends MedicalRecordEloquent implements HasMedia, Transformable
{
    use BelongsToPatientUser;
    use
        HasMediaTrait;
    use
        SoftDeletes;
    use
        TransformableTrait;
    const API = 'api';

    //define sources here
    const ATHENA_API = 'athena_api';

    const EMAIL_DOMAIN_TO_VENDOR_MAP = [
        //Carolina Medical Associates
        '@direct.novanthealth.org'        => 10,
        '@test.directproject.net'         => 14,
        '@direct.welltrackone.com'        => 14,
        '@treatrelease.direct.aprima.com' => 1,
    ];
    const EMR_DIRECT   = 'emr_direct';
    const GOOGLE_DRIVE = 'google_drive';
    const IMPORTER     = 'importer';
    const SFTP_DROPBOX = 'sftp_dropbox';
    const UPLOADED     = 'uploaded';

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
        'vendor_id',
        'source',
        'imported',
        'json',
        'xml',
        'status',
    ];

    public function bluebuttonJson()
    {
        if ($this->json) {
            return json_decode($this->json);
        }

        if ( ! $this->id || ! $this->hasMedia('ccd')) {
            return false;
        }

        if ( ! $this->json) {
            $xml = $this->getMedia('ccd')->first()->getFile();
            if ( ! is_string($xml) || strlen($xml) < 1 || false == stripos($xml, '<ClinicalDocument')) {
                throw new \Exception("CCD appears to be invalid. CCD: `$xml`");
            }
            $this->json = $this->parseToJson($xml);
            $this->save();
        }

        return json_decode($this->json);
    }

    public function ccdaRequest()
    {
        return $this->hasOne(CcdaRequest::class);
    }

    public static function create($attributes = [])
    {
        if ( ! array_key_exists('xml', $attributes)) {
            return static::query()->create($attributes);
        }

        $xml = $attributes['xml'];
        unset($attributes['xml']);

        $ccda = static::query()->create($attributes);

        \Storage::disk('storage')->put("ccda-{$ccda->id}.xml", $xml);
        $ccda->addMedia(storage_path("ccda-{$ccda->id}.xml"))->toMediaCollection('ccd');

        return $ccda;
    }

    public function directMessage()
    {
        return $this->belongsTo(DirectMailMessage::class);
    }

    /**
     * @return string
     */
    public function getDocumentCustodian(): string
    {
        if ($this->document->first()) {
            return $this->document->first()->custodian;
        }

        return '';
    }

    /**
     * Get the Logger.
     *
     * @return MedicalRecordLogger
     */
    public function getLogger(): MedicalRecordLogger
    {
        return new CcdaSectionsLogger($this);
    }

    /**
     * Get the User to whom this record belongs to, if one exists.
     *
     * @return \CircleLinkHealth\Customer\Entities\User
     */
    public function getPatient(): User
    {
        return $this->patient;
    }

    public function importedMedicalRecord()
    {
        return ImportedMedicalRecord::where('medical_record_type', '=', Ccda::class)
            ->where('medical_record_id', '=', $this->id)
            ->first();
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

    public function storeCcd($xml)
    {
        if ( ! $this->id) {
            throw new \Exception('CCD does not have an id.');
        }

        \Storage::disk('storage')->put("ccda-{$this->id}.xml", $xml);
        $this->addMedia(storage_path("ccda-{$this->id}.xml"))->toMediaCollection('ccd');

        return $this;
    }

    protected function parseToJson($xml)
    {
        $id = $this->id ?? '';

        if ( ! is_string($xml) || strlen($xml) < 1 || false == stripos($xml, '<ClinicalDocument')) {
            throw new \Exception("CCD with ${id} appears to be invalid.");
        }

        $client = new Client([
            'base_uri' => config('services.ccd-parser.base-uri'),
        ]);

        $response = $client->request('POST', '/api/parser', [
            'headers' => ['Content-Type' => 'text/xml', 'CCDA-ID' => $this->id ?? 'N/A'],
            'body'    => $xml,
        ]);

        $responseBody = (string) $response->getBody();

        if ( ! in_array($response->getStatusCode(), [200, 201])) {
            $data = json_encode([
                $response->getStatusCode(),
                $response->getReasonPhrase(),
            ]);

            throw new \Exception("Could not process ccd ${id}. Data: ${data}");
        }

        return $responseBody;
    }
}
