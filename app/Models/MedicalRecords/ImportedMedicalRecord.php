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
use App\Importer\Models\ItemLogs\ProviderLog;
use App\Location;
use App\Practice;
use App\Scopes\Universal\MedicalRecordIdAndTypeTrait;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImportedMedicalRecord extends Model implements ImportedMedicalRecordInterface
{
    use MedicalRecordIdAndTypeTrait,
        SoftDeletes;

    protected $fillable = [
        'medical_record_type',
        'medical_record_id',
        'billing_provider_id',
        'location_id',
        'practice_id',
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

    public function medicalRecord() : MedicalRecord
    {
        return app($this->medical_record_type)->find($this->medical_record_id);
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
}
