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

    public function demographics()
    {
        return $this->hasOne(DemographicsImport::class);
    }

    /**
     * Get the Medications that were imported for QA
     *
     * @return \App\Importer\Models\ImportedItems\MedicationImport[]
     */
    public function medications()
    {
        return $this->hasMany(MedicationImport::class);
    }

    /**
     * Get the Problems that were imported for QA
     *
     * @return \App\Importer\Models\ImportedItems\ProblemImport[]
     */
    public function problems()
    {
        return $this->hasMany(ProblemImport::class);
    }

    public function medicalRecord() : MedicalRecord
    {
        return app($this->medical_record_type)->find($this->medical_record_id);
    }

    public function practice() : Practice
    {
        return Practice::find($this->practice_id);
    }

    public function providers() : array
    {
        // TODO: Implement providers() method.
    }

    public function billingProvider() : User
    {
        return User::find($this->billing_provider_id);
    }

    public function createCarePlan() : CarePlan
    {
        $user = (new CCDImporterRepository())->createRandomUser($this->demographics);

        $this->patient_id = $user->id;
        $this->save();

        $helper = new CarePlanHelper($user, $this);
        $helper->storeImportedValues();
    }

    public function reimport() : ImportedMedicalRecordInterface
    {
        // TODO: Implement reimport() method.
    }
}
