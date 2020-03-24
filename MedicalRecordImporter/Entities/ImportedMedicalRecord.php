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
 * CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord.
 *
 * @property int                                                                                            $id
 * @property int|null                                                                                       $patient_id
 * @property string                                                                                         $medical_record_type
 * @property int                                                                                            $medical_record_id
 * @property int|null                                                                                       $billing_provider_id
 * @property int|null                                                                                       $location_id
 * @property int|null                                                                                       $practice_id
 * @property int|null                                                                                       $duplicate_id
 * @property \Carbon\Carbon|null                                                                            $created_at
 * @property \Carbon\Carbon|null                                                                            $updated_at
 * @property string|null                                                                                    $deleted_at
 * @property \CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\AllergyImport[]|\Illuminate\Database\Eloquent\Collection    $allergies
 * @property \CircleLinkHealth\Customer\Entities\User|null                                                  $billingProvider
 * @property \CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\DemographicsImport                                          $demographics
 * @property \CircleLinkHealth\Customer\Entities\Location|null                                              $location
 * @property \CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\MedicationImport[]|\Illuminate\Database\Eloquent\Collection $medications
 * @property \CircleLinkHealth\Customer\Entities\Practice|null                                              $practice
 * @property \CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ProblemImport[]|\Illuminate\Database\Eloquent\Collection    $problems
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord whereBillingProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord whereMedicalRecordId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord whereMedicalRecordType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord wherePracticeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord withMedicalRecord($id, $type = 'CircleLinkHealth\SharedModels\Entities\Ccda')
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord withoutTrashed()
 * @mixin \Eloquent
 * @property array|null                                                                     $validation_checks
 * @property \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Revisionable\Entities\Revision[] $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord whereDuplicateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord whereValidationChecks($value)
 * @property int|null $allergies_count
 * @property int|null $medications_count
 * @property int|null $problems_count
 * @property int|null $revision_history_count
 * @property-read \CircleLinkHealth\Customer\Entities\User|null $patient
 * @property int|null $nurse_user_id
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities\ImportedMedicalRecord whereNurseUserId($value)
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

    protected $casts = [
        'validation_checks' => 'array',
    ];

    protected $fillable = [
        'patient_id',
        'medical_record_type',
        'medical_record_id',
        'billing_provider_id',
        'location_id',
        'practice_id',
        'duplicate_id',
        'validation_checks',
    ];

    public function allergies()
    {
        return $this->hasMany(AllergyImport::class);
    }

    public function billingProvider()
    {
        return $this->belongsTo(User::class, 'billing_provider_id', 'id');
    }

    /**
     * @todo: duplicate of Importer/MedicalRecordEloquent.php @ raiseConcerns()
     *
     * @return int|mixed|null
     */
    public function checkDuplicity()
    {
        $demos = $this->demographics()->first();

        if ($demos) {
            $practiceId = $this->practice_id;

            $query = User::whereFirstName($demos->first_name)
                ->whereLastName($demos->last_name)
                ->whereHas('patientInfo', function ($q) use ($demos) {
                    $q->whereBirthDate($demos->dob);
                });
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
            })->whereMrnNumber($demos->mrn_number)->first();

            if ($patient) {
                $this->duplicate_id = $patient->user_id;

                return $patient->user_id;
            }

            return null;
        }
    }

    public function createCarePlan(): CarePlan
    {
        $user = (new CCDImporterRepository())->createRandomUser($this->demographics, $this);

        $this->patient_id = $user->id;
        $this->save();

        $helper = new CarePlanHelper($user, $this);

        return $helper->storeImportedValues();
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
    
    public function updateOrCreateCarePlan(): CarePlan
    {
        // TODO: Implement updateOrCreateCarePlan() method.
    }
}
