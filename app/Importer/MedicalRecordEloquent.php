<?php namespace App\Importer;

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 16/01/2017
 * Time: 4:05 PM
 */

use App\Contracts\Importer\MedicalRecord\MedicalRecord;
use App\Importer\Predictors\HistoricLocationPredictor;
use App\Importer\Predictors\HistoricPracticePredictor;
use App\Importer\Section\Importers\Allergies;
use App\Importer\Section\Importers\Demographics;
use App\Importer\Section\Importers\Insurance;
use App\Importer\Section\Importers\Medications;
use App\Importer\Section\Importers\Problems;
use App\Models\MedicalRecords\ImportedMedicalRecord;
use App\Traits\MedicalRecordItemLoggerRelationships;
use Illuminate\Database\Eloquent\Model;


abstract class MedicalRecordEloquent extends Model implements MedicalRecord
{
    use MedicalRecordItemLoggerRelationships;

    /**
     * @var integer;
     */
    protected $billingProviderIdPrediction;

    /**
     * @var integer
     */
    protected $locationIdPrediction;

    /**
     * @var integer
     */
    protected $practiceIdPrediction;

    /**
     * Handles importing a MedicalRecord for QA.
     *
     * @return ImportedMedicalRecord
     *
     */
    public function import()
    {
        $this->createLogs()
            ->predictPractice()
            ->predictLocation()
            ->predictBillingProvider()
            ->createImportedMedicalRecord()
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
            'billing_provider_id' => $this->getBillingProviderIdPrediction(),
            'location_id'         => $this->getLocationIdPrediction(),
            'practice_id'         => $this->getPracticeIdPrediction(),
        ]);

        return $this;
    }

    /**
     * @return mixed
     */
    abstract public function getBillingProviderIdPrediction();

    /**
     * @param mixed $billingProvider
     *
     * @return MedicalRecord
     */
    abstract public function setBillingProviderIdPrediction($billingProvider) : MedicalRecord;

    /**
     * @return mixed
     */
    abstract public function getLocationIdPrediction();

    /**
     * @param mixed $location
     *
     * @return MedicalRecord
     */
    abstract public function setLocationIdPrediction($location) : MedicalRecord;

    /**
     * @return mixed
     */
    public function getPracticeIdPrediction()
    {
        return $this->practiceIdPrediction;
    }

    /**
     * @param mixed $practice
     *
     * @return MedicalRecord
     */
    abstract public function setPracticeIdPrediction($practice) : MedicalRecord;

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
        if ($this->getPracticeIdPrediction()) {
            return $this;
        }

        //historic lookup
        $historicPredictor = new HistoricPracticePredictor($this->document->custodian, $this->providers);
        $historicPrediction = $historicPredictor->predict();

        if ($historicPrediction) {
            $this->setPracticeIdPrediction($historicPrediction);

            return $this;
        }


        return $this;
    }

    /**
     * Predict which Location should be attached to this MedicalRecord.
     *
     * @return MedicalRecord
     */
    public function predictLocation() : MedicalRecord
    {
        if ($this->getLocationIdPrediction()) {
            return $this;
        }
        //historic lookup
        $historicPredictor = new HistoricLocationPredictor($this->document->custodian, $this->providers);
        $historicPrediction = $historicPredictor->predict();

        if ($historicPrediction) {
            $this->setLocationIdPrediction($historicPrediction);

            return $this;
        }


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