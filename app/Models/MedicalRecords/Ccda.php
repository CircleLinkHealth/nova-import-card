<?php namespace App\Models\MedicalRecords;

use App\Contracts\Importer\MedicalRecord\MedicalRecordLogger;
use App\Entities\CcdaRequest;
use App\Importer\Loggers\Ccda\CcdaSectionsLogger;
use App\Importer\MedicalRecordEloquent;
use App\Traits\Relationships\BelongsToPatientUser;
use App\User;
use Cache;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\SoftDeletes;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * App\Models\MedicalRecords\Ccda
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
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Importer\Models\ItemLogs\AllergyLog[] $allergies
 * @property-read \App\Entities\CcdaRequest $ccdaRequest
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Importer\Models\ItemLogs\DemographicsLog[] $demographics
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Importer\Models\ImportedItems\DemographicsImport[] $demographicsImports
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Importer\Models\ItemLogs\DocumentLog[] $document
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Importer\Models\ItemLogs\MedicationLog[] $medications
 * @property-read \App\User|null $patient
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Importer\Models\ItemLogs\ProblemLog[] $problems
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Importer\Models\ItemLogs\ProviderLog[] $providers
 * @property-read \App\Models\MedicalRecords\ImportedMedicalRecord $qaSummary
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
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereReferringProviderName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereVendorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\Ccda whereXml($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MedicalRecords\Ccda withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MedicalRecords\Ccda withoutTrashed()
 * @mixin \Eloquent
 */
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
        'date',
    ];

    protected $fillable = [
        'date',
        'mrn',
        'referring_provider_name',
        'location_id' .
        'practice_id',
        'billing_provider_id',
        'user_id',
        'patient_id',
        'vendor_id',
        'source',
        'imported',
        'xml',
        'json',
        'status',
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
    public function getLogger(): MedicalRecordLogger
    {
        return new CcdaSectionsLogger($this);
    }

    /**
     * Get the User to whom this record belongs to, if one exists.
     *
     * @return User
     */
    public function getPatient(): User
    {
        return $this->patient;
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

    public function bluebuttonJson()
    {
        if (!$this->id && !$this->xml) {
            return false;
        }

        $key = "ccda:{$this->id}:json";

        return Cache::remember($key, 7000, function () {
            if (!$this->json) {
                $this->json = $this->parseToJson($this->xml);
                $this->save();
            }

            return json_decode($this->json);
        });
    }

    protected function parseToJson($xml)
    {
        $client = new Client([
            'base_uri' => env('CCD_PARSER_BASE_URI', 'https://circlelink-ccd-parser.medstack.net'),
        ]);

        $response = $client->request('POST', '/ccda/parse', [
            'headers' => ['Content-Type' => 'text/xml'],
            'body'    => $xml,
        ]);

        if ($response->getStatusCode() != 200) {
            return [
                $response->getStatusCode(),
                $response->getReasonPhrase(),
            ];
        }

        return (string)$response->getBody();
    }
}
