<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

use App\CLH\Repositories\CCDImporterRepository;
use App\Console\Commands\OverwriteNBIImportedData;
use App\DirectMailMessage;
use App\Entities\CcdaRequest;
use App\Search\ProviderByName;
use App\Traits\Relationships\BelongsToPatientUser;
use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Core\Exceptions\InvalidCcdaException;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Adapters\CcdaToEligibilityJobAdapter;
use CircleLinkHealth\Eligibility\CcdaImporter\CcdaImporter;
use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\Eligibility\Entities\TargetPatient;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\MedicalRecord;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Events\CcdaImported;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

class Ccda extends BaseModel implements HasMedia, MedicalRecord
{
    /**
     * For 'type' column, for G0506.
     */
    const COMPREHENSIVE_ASSESSMENT_TYPE = 'comprehensive_assessment';
    
    /**
     * An option in validation_checks.
     */
    const CHECK_HAS_AT_LEAST_1_BHI_CONDITION = 'has_at_least_1_bhi_condition';
    /**
     * An option in validation_checks.
     */
    const CHECK_HAS_AT_LEAST_1_CCM_CONDITION = 'has_at_least_1_ccm_condition';
    /**
     * An option in validation_checks.
     */
    const CHECK_HAS_AT_LEAST_2_CCM_CONDITIONS = 'has_at_least_2_ccm_conditions';
    /**
     * An option in validation_checks.
     */
    const CHECK_HAS_MEDICARE = 'has_medicare';
    /**
     * An option in validation_checks.
     * Indicates whether or not this patient's data was overwritten from additional data we received from the practice.
     * Currently this only applies to NBI.
     */
    const WAS_NBI_OVERWRITTEN = 'was_nbi_overwritten';
    /**
     * An option in validation_checks.
     * Indicates whether CLH can offer PCM service to the patient, if practice has PCM enabled.
     */
    const CHECK_PRACTICE_HAS_PCM = 'practice_has_pcm';
    
    protected $casts = [
        'validation_checks' => 'array',
    ];
    
    private $decodedJson;
    
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
        'duplicate_id',
        'validation_checks',
    ];
    
    public function batch()
    {
        return $this->belongsTo(EligibilityBatch::class);
    }
    
    public function bluebuttonJson()
    {
        if (!empty($this->decodedJson)) {
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
    
    public function getDocumentCustodian(): string
    {
        if ($this->document->first()) {
            return $this->document->first()->custodian;
        }
        
        return '';
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
    
    public function practice()
    {
        return $this->belongsTo(Practice::class);
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
        $this->mrn  = $this->patientMrn();
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
    
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
    
    public function scopeHasUPG0506PdfCareplanMedia($query)
    {
        return $query->whereExists(
            function ($query) {
                $query->select('id')
                      ->from('media')
                      ->where('custom_properties->is_pdf', 'true')->where(
                        'custom_properties->is_upg0506',
                        'true'
                    )->where('custom_properties->care_plan->demographics->mrn_number', (string) $this->mrn);
            }
        );
    }
    
    public function updateOrCreateCarePlan(): CarePlan
    {
        return (new CcdaImporter($this, $this->load('patient')->patient ?? null))->attemptCreateCarePlan();
    }
    
    public function patientEmail()
    {
        return $this->bluebuttonJson()->demographics->email;
    }
    
    public function patientFirstName()
    {
        return $this->bluebuttonJson()->demographics->name->given[0] ?? null;
    }
    
    public function patientLastName()
    {
        return $this->bluebuttonJson()->demographics->name->family;
    }
    
    /**
     * A collection.
     *
     * @var Collection
     */
    protected $insurances;
    
    /**
     * @return mixed
     */
    public function getBillingProviderId(): ?int
    {
        return $this->billing_provider_id;
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
     * @return mixed
     */
    public function getPracticeId(): ?int
    {
        return $this->practice_id;
    }
    
    public function getType(): ?string
    {
        return get_class($this);
    }
    
    public function guessPracticeLocationProvider(): MedicalRecord
    {
        if ($term = $this->getReferringProviderName()) {
            $this->setAllPracticeInfoFromProvider($term);
        }
        
        if ($this->isDirty()) {
            $this->save();
        }
        
        return $this;
    }
    
    /**
     * Handles importing a MedicalRecordForEligibilityCheck for QA.
     *
     * @return Ccda
     */
    public function import()
    {
        $this
            ->guessPracticeLocationProvider();
        
        $this->updateOrCreateCarePlan();
        $this->raiseConcerns();
        
        event(new CcdaImported($this->getId()));
        
        $this->imported = true;
        $this->status   = Ccda::QA;
        $this->save();
        
        return $this;
    }
    
    public function raiseConcerns()
    {
        $this->load(['patient.ccdProblems', 'patient.ccdInsurancePolicies']);

        $isDuplicate             = $this->isDuplicate();
        $ccmConditionsCount      = $this->ccmConditionsCount();
        $hasAtLeast1BhiCondition = $this->hasAtLeast1BhiCondition();
        $hasMedicare             = $this->hasMedicare();
        $wasNBIOverwritten       = app(OverwriteNBIImportedData::class)->lookupAndReplacePatientData(
            $this
        );
        
        $practiceHasPcm = null;
        $practiceId     = $this->getPracticeId();
        if ($practiceId) {
            $practice = Practice::with('chargeableServices')->find($practiceId);
            if ($practice) {
                $practiceHasPcm = $practice->hasServiceCode(ChargeableService::PCM);
            }
        }
        
        $this->validation_checks = [
            self::CHECK_HAS_AT_LEAST_1_CCM_CONDITION  => $ccmConditionsCount >= 1,
            self::CHECK_HAS_AT_LEAST_2_CCM_CONDITIONS => $ccmConditionsCount >= 2,
            self::CHECK_HAS_AT_LEAST_1_BHI_CONDITION  => $hasAtLeast1BhiCondition,
            self::CHECK_PRACTICE_HAS_PCM              => $practiceHasPcm,
            self::CHECK_HAS_MEDICARE                  => $hasMedicare,
            self::WAS_NBI_OVERWRITTEN                 => $wasNBIOverwritten,
        ];
    }
    
    /**
     * @return int|mixed|null
     * @todo: duplicate of Importer/MedicalRecordEloquent.php @ raiseConcerns()
     *
     */
    public function checkDuplicity()
    {
        $newUser = User::ofType('participant')->find($this->patient_id);
        
        if ($newUser) {
            $this->duplicate_id = null;
            
            $practiceId = $this->practice_id;
            
            $query = User::whereFirstName($newUser->first_name)
                         ->whereLastName($newUser->last_name)
                         ->whereHas(
                             'patientInfo',
                             function ($q) use ($newUser) {
                                 $q->where('birth_date', $newUser->getBirthDate());
                             }
                         )->where('id', '!=', $newUser->id);
            if ($practiceId) {
                $query = $query->where('program_id', $practiceId);
            }
            
            $user = $query->first();
            
            if ($user) {
                $this->duplicate_id = $user->id;
                
                return $user->id;
            }
            
            $patient = Patient::whereHas(
                'user',
                function ($q) use ($practiceId) {
                    $q->where('program_id', $practiceId);
                }
            )->whereMrnNumber($newUser->getMRN())->whereNotNull('mrn_number')->where(
                'user_id',
                '!=',
                $newUser->id
            )->first();
            
            if ($patient) {
                $this->duplicate_id = $patient->user_id;
                
                return $patient->user_id;
            }
            
            return null;
        }
    }
    
    /**
     * Search for a Billing Provider using a search term, and.
     */
    public function searchBillingProvider(string $term): ?User
    {
        $baseQuery = (new ProviderByName())->query($term);
        
        if ('algolia' === config('scout.driver')) {
            return $baseQuery
                ->with(
                    [
                        'typoTolerance' => true,
                    ]
                )->when(
                    ! empty($this->practice_id),
                    function ($q) {
                        $q->whereIn('practice_ids', [$this->practice_id]);
                    }
                )
                ->first();
        }
        
        return $baseQuery->when(
            ! empty($this->practice_id),
            function ($q) {
                $q->ofPractice($this->practice_id);
            }
        )->first();
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
    public function setLocationId($locationId): MedicalRecord
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
    
    /**
     * @return bool
     */
    private function hasAtLeast1BhiCondition()
    {
        if (!$this->patient) return;
        
        return $this->patient->ccdProblems->where('is_monitored', true)
                                      ->unique('cpm_problem_id')
                                      ->where('is_behavioral', true)
                                      ->count() >= 1;
    }
    
    /**
     * @return bool
     */
    private function hasAtLeast2CcmConditions()
    {
        if (!$this->patient) return;
        
        return $this->patient->ccdProblems->where('is_monitored', true)
                                      ->unique('cpm_problem_id')
                                      ->count() >= 2;
    }
    
    /**
     * @return int
     */
    private function ccmConditionsCount()
    {
        if (!$this->patient) return;
        
        return $this->patient->ccdProblems->where('is_monitored', true)
                                      ->unique('cpm_problem_id')
                                      ->count();
    }
    
    /**
     * @return bool
     */
    private function hasMedicare()
    {
        if (!$this->patient) return;
        
        return $this->patient->ccdInsurancePolicies->reject(
                function ($i) {
                    return ! str_contains(strtolower($i->name.$i->type), 'medicare');
                }
            )
                                ->count() >= 1;
    }
    
    /**
     * Checks whether the patient we have just imported exists in the system.
     *
     * @return bool|null
     */
    private function isDuplicate()
    {
        $practiceId = $this->practice_id;
        
        $query = User::whereFirstName($this->patientFirstName())
                     ->whereLastName($this->patientLastName())
                     ->whereHas(
                         'patientInfo',
                         function ($q) {
                             $q->whereBirthDate($this->patientDob());
                         }
                     );
        if ($practiceId) {
            $query = $query->where('program_id', $practiceId);
        }
        
        $user = $query->first();
        
        if ($user && (int) $this->duplicate_id !== (int) $user->id) {
            $this->duplicate_id = $user->id;
            $this->save();
            
            return true;
        }
        
        $patient = Patient::whereHas(
            'user',
            function ($q) use ($practiceId) {
                $q->where('program_id', $practiceId);
            }
        )->whereMrnNumber($this->patientMrn())->first();
        
        if ($patient && (int) $this->duplicate_id !== (int) $user->id) {
            $this->duplicate_id = $patient->user_id;
            $this->save();
            
            return true;
        }
        
        return false;
    }
    
    private function setAllPracticeInfoFromProvider(string $term)
    {
        $searchProvider = $this->searchBillingProvider($term);
        
        if ( ! $searchProvider) {
            return;
        }
        
        if ( ! $this->getPracticeId()) {
            $this->setPracticeId($searchProvider->program_id);
        }
        
        $this->setBillingProviderId($searchProvider->id);
        $this->setLocationId(optional($searchProvider->loadMissing('locations')->locations->first())->id);
    }
    
    public function patientDob()
    {
        return $this->bluebuttonJson()->demographics->dob;
    }
    
    public function patientMrn()
    {
        return $this->bluebuttonJson()->demographics->mrn_number;
    }
}
