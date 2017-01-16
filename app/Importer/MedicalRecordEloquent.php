<?php namespace App\Importer;

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 16/01/2017
 * Time: 4:05 PM
 */

use App\Contracts\Importer\MedicalRecord\MedicalRecord;
use App\Importer\Models\ItemLogs\DocumentLog;
use App\Importer\Models\ItemLogs\ProviderLog;
use App\Importer\Section\Importers\Allergies;
use App\Importer\Section\Importers\Demographics;
use App\Importer\Section\Importers\Insurance;
use App\Importer\Section\Importers\Medications;
use App\Importer\Section\Importers\Problems;
use App\Location;
use App\Models\MedicalRecords\ImportedMedicalRecord;
use App\Practice;
use App\Traits\MedicalRecordItemLoggerRelationships;
use Illuminate\Database\Eloquent\Model;


abstract class MedicalRecordEloquent extends Model implements MedicalRecord
{
    use MedicalRecordItemLoggerRelationships;

    /**
     * @var ProviderLog;
     */
    protected $billingProviderPrediction;

    /**
     * @var Location
     */
    protected $locationPrediction;

    /**
     * @var Practice
     */
    protected $practicePrediction;

    /**
     * Handles importing a MedicalRecord for QA.
     *
     * @return ImportedMedicalRecord
     *
     */
    public function import()
    {
        $this->createLogs()
            ->createImportedMedicalRecord()
            ->predictPractice()
            ->predictLocation()
            ->predictBillingProvider()
            ->importAllergies()
            ->importDemographics()
            ->importDocument()
            ->importInsurance()
            ->importMedications()
            ->importProblems()
            ->importProviders();
    }

    /**
     * Log the data into MedicalRecordSectionLogs, so that they can be fed to the Importer
     *
     * @return MedicalRecord
     */
    public function createLogs() : MedicalRecord
    {
        $this->getLogger()->logAllSections();

        return $this;
    }

    /**
     * @return MedicalRecord
     */
    public function createImportedMedicalRecord() : MedicalRecord
    {
        $this->importedMedicalRecord = ImportedMedicalRecord::create([
            'medical_record_type' => get_class($this),
            'medical_record_id'   => $this->id,
            'billing_provider_id' => null,
            'location_id'         => null,
            'practice_id'         => null,
        ]);

        return $this;
    }

    /**
     * Import Allergies for QA
     *
     * @return MedicalRecord
     */
    public function importAllergies() : MedicalRecord
    {
        $importer = new Allergies();
        $importer->import($this->id, get_class($this), $this->importedMedicalRecord);

        return $this;
    }

    /**
     * Import Demographics for QA
     *
     * @return \App\Contracts\Importer\MedicalRecord\MedicalRecord
     */
    public function importDemographics() : MedicalRecord
    {
        $importer = new Demographics();
        $importer->import($this->id, get_class($this), $this->importedMedicalRecord);

        return $this;
    }

    /**
     * Import Document for QA
     *
     * @return \App\Contracts\Importer\MedicalRecord\MedicalRecord
     */
    public function importDocument() : MedicalRecord
    {
        return $this;

    }

    /**
     * Import Medications for QA
     *
     * @return \App\Contracts\Importer\MedicalRecord\MedicalRecord
     */
    public function importMedications() : MedicalRecord
    {
        $importer = new Medications();
        $importer->import($this->id, get_class($this), $this->importedMedicalRecord);

        return $this;
    }

    /**
     * Import Problems for QA
     *
     * @return \App\Contracts\Importer\MedicalRecord\MedicalRecord
     */
    public function importProblems() : MedicalRecord
    {
        $importer = new Problems();
        $importer->import($this->id, get_class($this), $this->importedMedicalRecord);

        return $this;
    }

    /**
     * Import Providers for QA
     *
     * @return \App\Contracts\Importer\MedicalRecord\MedicalRecord
     */
    public function importProviders() : MedicalRecord
    {
        return $this;
    }

    /**
     * Import Insurance Policies for QA
     *
     * @return MedicalRecord
     */
    public function importInsurance() : MedicalRecord
    {
        $importer = new Insurance();
        $importer->import($this->id, get_class($this), $this->importedMedicalRecord);

        return $this;
    }

    /**
     * Predict which Practice should be attached to this MedicalRecord.
     *
     * @return MedicalRecord
     */
    public function predictPractice() : MedicalRecord
    {
        //historic custodian lookup
        $custodianLookup = DocumentLog::where('custodian', '=', $this->document->custodian)
            ->whereNotNull('practice_id')
            ->groupBy('practice_id')
            ->get(['practice_id'])
            ->keyBy('practice_id')
            ->keys();


        return $this;
    }

    /**
     * Predict which Location should be attached to this MedicalRecord.
     *
     * @return MedicalRecord
     */
    public function predictLocation() : MedicalRecord
    {
        //historic custodian lookup
        $custodianLookup = DocumentLog::where('custodian', '=', $this->document->custodian)
            ->whereNotNull('location_id')
            ->groupBy('location_id')
            ->get(['location_id']);


        return $this;
    }

    /**
     * Predict which BillingProvider should be attached to this MedicalRecord.
     *
     * @return MedicalRecord
     */
    public function predictBillingProvider() : MedicalRecord
    {
        // TODO: Implement predictBillingProvider() method.
        return $this;
    }


}