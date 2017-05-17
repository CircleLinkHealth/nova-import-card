<?php namespace App\Importer;

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 16/01/2017
 * Time: 4:05 PM
 */

use App\Contracts\Importer\MedicalRecord\MedicalRecord;
use App\Importer\Predictors\HistoricBillingProviderPredictor;
use App\Importer\Predictors\HistoricLocationPredictor;
use App\Importer\Predictors\HistoricPracticePredictor;
use App\Importer\Section\Importers\Allergies;
use App\Importer\Section\Importers\Demographics;
use App\Importer\Section\Importers\Insurance;
use App\Importer\Section\Importers\Medications;
use App\Importer\Section\Importers\Problems;
use App\Models\MedicalRecords\ImportedMedicalRecord;
use App\Traits\Relationships\MedicalRecordItemLoggerRelationships;
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

        return $this->importedMedicalRecord;
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
    public function getBillingProviderIdPrediction()
    {
        return $this->billingProviderIdPrediction;
    }

    /**
     * @param mixed $billingProviderId
     *
     * @return MedicalRecord
     */
    public function setBillingProviderIdPrediction($billingProviderId) : MedicalRecord
    {
        $this->billingProviderIdPrediction = $billingProviderId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLocationIdPrediction()
    {
        return $this->locationIdPrediction;
    }

    /**
     * @param mixed $locationId
     *
     * @return MedicalRecord
     */
    public function setLocationIdPrediction($locationId) : MedicalRecord
    {
        $this->locationIdPrediction = $locationId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPracticeIdPrediction()
    {
        return $this->practiceIdPrediction;
    }

    /**
     * @param mixed $practiceId
     *
     * @return MedicalRecord
     */
    public function setPracticeIdPrediction($practiceId) : MedicalRecord
    {
        $this->practiceIdPrediction = $practiceId;

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
        $importer = new Medications($this->id, get_class($this), $this->importedMedicalRecord);
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
        if ($this->practice_id) {
            $this->setPracticeIdPrediction($this->practice_id);

            return $this;
        }

        if ($this->getPracticeIdPrediction()) {
            return $this;
        }

        //historic lookup
        $historicPredictor = new HistoricPracticePredictor($this->getDocumentCustodian(), $this->providers);
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
        $historicPredictor = new HistoricLocationPredictor($this->getDocumentCustodian(), $this->providers);
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
        if ($this->billing_provider_id) {
            $this->setBillingProviderIdPrediction($this->billing_provider_id);

            return $this;
        }


        if ($this->getBillingProviderIdPrediction()) {
            return $this;
        }

        //historic lookup
        $historicPredictor = new HistoricBillingProviderPredictor($this->getDocumentCustodian(), $this->providers);
        $historicPrediction = $historicPredictor->predict();

        if ($historicPrediction) {
            $this->setBillingProviderIdPrediction($historicPrediction);

            return $this;
        }


        return $this;
    }


}