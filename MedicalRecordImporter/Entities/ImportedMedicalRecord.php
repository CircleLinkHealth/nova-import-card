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
            $practiceId = $this->practice_id;

            $query = User::whereFirstName($newUser->first_name)
                ->whereLastName($newUser->last_name)
                ->whereHas('patientInfo', function ($q) use ($newUser) {
                    $q->where('birth_date', $newUser->dob);
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
