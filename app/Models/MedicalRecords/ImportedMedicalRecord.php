<?php namespace App\Models\MedicalRecords;

use App\CarePlan;
use App\CLH\Repositories\CCDImporterRepository;
use App\Contracts\Importer\ImportedMedicalRecord\ImportedMedicalRecord as ImportedMedicalRecordInterface;
use App\Contracts\Importer\MedicalRecord\MedicalRecord;
use App\Importer\CarePlanHelper;
use App\Importer\Models\ImportedItems\AllergyImport;
use App\Importer\Models\ImportedItems\DemographicsImport;
use App\Importer\Models\ImportedItems\MedicationImport;
use App\Importer\Models\ImportedItems\ProblemImport;
use App\Location;
use App\Patient;
use App\Practice;
use App\Scopes\Universal\MedicalRecordIdAndTypeTrait;
use App\User;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\MedicalRecords\ImportedMedicalRecord
 *
 * @property int $id
 * @property int|null $patient_id
 * @property string $medical_record_type
 * @property int $medical_record_id
 * @property int|null $billing_provider_id
 * @property int|null $location_id
 * @property int|null $practice_id
 * @property int|null $duplicate_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Importer\Models\ImportedItems\AllergyImport[] $allergies
 * @property-read \App\User|null $billingProvider
 * @property-read \App\Importer\Models\ImportedItems\DemographicsImport $demographics
 * @property-read \App\Location|null $location
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Importer\Models\ImportedItems\MedicationImport[] $medications
 * @property-read \App\Practice|null $practice
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Importer\Models\ImportedItems\ProblemImport[] $problems
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
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord withMedicalRecord($id, $type = 'App\Models\MedicalRecords\Ccda')
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MedicalRecords\ImportedMedicalRecord withoutTrashed()
 * @mixin \Eloquent
 */
class ImportedMedicalRecord extends \App\BaseModel implements ImportedMedicalRecordInterface
{
    use MedicalRecordIdAndTypeTrait,
        SoftDeletes;

    protected $fillable = [
        'patient_id',
        'medical_record_type',
        'medical_record_id',
        'billing_provider_id',
        'location_id',
        'practice_id',
        'duplicate_id'
    ];

    public function allergies()
    {
        return $this->hasMany(AllergyImport::class);
    }

    /**
     * Get the Demographics that were imported for QA
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function demographics()
    {
        return $this->hasOne(DemographicsImport::class);
    }

    /**
     * Get the Medications that were imported for QA
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function medications()
    {
        return $this->hasMany(MedicationImport::class);
    }

    /**
     * Get the Problems that were imported for QA
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function problems()
    {
        return $this->hasMany(ProblemImport::class);
    }

    /**
     * @return MedicalRecord|null
     */
    public function medicalRecord()
    {
        return (app($this->medical_record_type))->withTrashed()->find($this->medical_record_id);
    }

    public function getPractice() : Practice
    {
        return Practice::find($this->practice_id);
    }

    /**
     * @return array
     */
    public function providers() : array
    {
        // TODO: Implement providers() method.
    }

    public function getBillingProvider() : User
    {
        return User::find($this->billing_provider_id);
    }

    public function createCarePlan() : CarePlan
    {
        $user = (new CCDImporterRepository())->createRandomUser($this->demographics, $this);

        $this->patient_id = $user->id;
        $this->save();

        $helper = new CarePlanHelper($user, $this);

        return $helper->storeImportedValues();
    }

    public function reimport() : ImportedMedicalRecordInterface
    {
        // TODO: Implement reimport() method.
    }

    public function practice()
    {
        return $this->belongsTo(Practice::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
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
    public function checkDuplicity() {
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
}
