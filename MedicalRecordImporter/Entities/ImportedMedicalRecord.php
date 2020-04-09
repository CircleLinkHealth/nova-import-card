<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities;

use CircleLinkHealth\SharedModels\Entities\CarePlan;
use App\CLH\Repositories\CCDImporterRepository;
use CircleLinkHealth\Eligibility\Contracts\ImportedMedicalRecord as ImportedMedicalRecordInterface;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\MedicalRecord;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\CarePlanHelper;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\AllergyImport;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\DemographicsImport;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\MedicationImport;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProblemImport;
use CircleLinkHealth\Eligibility\Scopes\MedicalRecordIdAndTypeTrait;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

/**
 * CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord
 *
 * @property int $id
 * @property int|null $imported
 * @property int|null $patient_id
 * @property string $medical_record_type
 * @property int $medical_record_id
 * @property int|null $billing_provider_id
 * @property int|null $nurse_user_id
 * @property int|null $location_id
 * @property int|null $practice_id
 * @property int|null $duplicate_id
 * @property array|null $validation_checks
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\AllergyImport[] $allergies
 * @property-read int|null $allergies_count
 * @property-read \CircleLinkHealth\Customer\Entities\User|null $billingProvider
 * @property-read \CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\DemographicsImport $demographics
 * @property-read \CircleLinkHealth\Customer\Entities\Location|null $location
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\MedicationImport[] $medications
 * @property-read int|null $medications_count
 * @property-read \CircleLinkHealth\Customer\Entities\User|null $nurseUser
 * @property-read \CircleLinkHealth\Customer\Entities\User|null $patient
 * @property-read \CircleLinkHealth\Customer\Entities\Practice|null $practice
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProblemImport[] $problems
 * @property-read int|null $problems_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Revisionable\Entities\Revision[] $revisionHistory
 * @property-read int|null $revision_history_count
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord newQuery()
 * @method static \Illuminate\Database\Query\Builder|\CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord whereBillingProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord whereDuplicateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord whereImported($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord whereMedicalRecordId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord whereMedicalRecordType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord whereNurseUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord wherePracticeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord whereValidationChecks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord withMedicalRecord($id, $type = 'CircleLinkHealth\SharedModels\Entities\Ccda')
 * @method static \Illuminate\Database\Query\Builder|\CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord withoutTrashed()
 * @mixin \Eloquent
 */
class ImportedMedicalRecord extends \CircleLinkHealth\Core\Entities\BaseModel implements ImportedMedicalRecordInterface
{
    use MedicalRecordIdAndTypeTrait;
    use SoftDeletes;


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

    protected $fillable = [
        'imported',
        'patient_id',
        'medical_record_type',
        'medical_record_id',
        'billing_provider_id',
        'nurse_user_id',
        'location_id',
        'practice_id',
        'duplicate_id',
        'validation_checks',
    ];
    
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function allergies()
    {
        return $this->hasMany(AllergyImport::class);
    }

    public function billingProvider()
    {
        return $this->belongsTo(User::class, 'billing_provider_id', 'id');
    }
    
    public function nurseUser()
    {
        return $this->belongsTo(User::class, 'nurse_user_id', 'id');
    }

    /**
     * @todo: duplicate of Importer/MedicalRecordEloquent.php @ raiseConcerns()
     *
     * @return int|mixed|null
     */
    public function checkDuplicity()
    {
        $newUser = User::ofType('participant')->find($this->patient_id);

        if ($newUser) {
            $this->duplicate_id = null;
            
            $practiceId = $this->practice_id;

            $query = User::whereFirstName($newUser->first_name)
                ->whereLastName($newUser->last_name)
                ->whereHas('patientInfo', function ($q) use ($newUser) {
                    $q->where('birth_date', $newUser->getBirthDate());
                })->where('id','!=', $newUser->id);
            if ($practiceId) {
                $query = $query->where('program_id', $practiceId);
            }

            $user = $query->first();

            if ($user) {
                $this->duplicate_id = $user->id;

                return $user->id;
            }

            $patient = Patient::whereHas('user', function ($q) use ($practiceId) {
                $q->where('program_id', $practiceId);
            })->whereMrnNumber($newUser->getMRN())->whereNotNull('mrn_number')->where('user_id','!=', $newUser->id)->first();

            if ($patient) {
                $this->duplicate_id = $patient->user_id;

                return $patient->user_id;
            }

            return null;
        }
    }

    public function updateOrCreateCarePlan(): CarePlan
    {
        if (! $this->patient_id) {
            $user = (new CCDImporterRepository())->createRandomUser($this->demographics, $this);

            $this->patient_id = $user->id;
            $this->save();
        }

        return (new CarePlanHelper($user ?? $this->patient, $this))->storeImportedValues();
    }

    /**
     * Get the Demographics that were imported for QA.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function demographics()
    {
        return $this->hasOne(DemographicsImport::class);
    }

    public function getBillingProvider(): User
    {
        return User::find($this->billing_provider_id);
    }

    public function getPractice(): Practice
    {
        return Practice::find($this->practice_id);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * @return \CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\MedicalRecord|null
     */
    public function medicalRecord()
    {
        return (app($this->medical_record_type))->withTrashed()->find($this->medical_record_id);
    }

    /**
     * Get the Medications that were imported for QA.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function medications()
    {
        return $this->hasMany(MedicationImport::class);
    }

    public function practice()
    {
        return $this->belongsTo(Practice::class);
    }

    /**
     * Get the Problems that were imported for QA.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function problems()
    {
        return $this->hasMany(ProblemImport::class);
    }

    /**
     * @return array
     */
    public function providers(): array
    {
        // TODO: Implement providers() method.
    }

    public function reimport(): ImportedMedicalRecordInterface
    {
        // TODO: Implement reimport() method.
    }
    
    public function createCarePlan(): CarePlan
    {
        // TODO: Implement createCarePlan() method.
    }
}
