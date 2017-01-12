<?php namespace App\Models\MedicalRecords;

use App\CarePlan;
use App\Contracts\Importer\ImportedMedicalRecord\ImportedMedicalRecord as ImportedMedicalRecordInterface;
use App\Contracts\Importer\MedicalRecord\MedicalRecord;
use App\Importer\Models\ImportedItems\AllergyImport;
use App\Importer\Models\ImportedItems\DemographicsImport;
use App\Practice;
use App\User;
use Illuminate\Database\Eloquent\Model;

class ImportedMedicalRecord extends Model implements ImportedMedicalRecordInterface
{
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
        // TODO: Implement medications() method.
    }

    /**
     * Get the Problems that were imported for QA
     *
     * @return \App\Importer\Models\ImportedItems\ProblemImport[]
     */
    public function problems()
    {
        // TODO: Implement problems() method.
    }

    public function medicalRecord() : MedicalRecord
    {
        // TODO: Implement medicalRecord() method.
    }

    public function practice() : Practice
    {
        // TODO: Implement practice() method.
    }

    public function providers() : array
    {
        // TODO: Implement providers() method.
    }

    public function billingProvider() : User
    {
        // TODO: Implement billingProvider() method.
    }

    public function createCarePLan() : CarePlan
    {
        // TODO: Implement createCarePLan() method.
    }

    public function reimport() : ImportedMedicalRecordInterface
    {
        // TODO: Implement reimport() method.
    }
}
