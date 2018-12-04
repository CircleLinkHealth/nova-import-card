<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer;

/*
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
use App\Patient;
use App\Practice;
use App\Traits\Relationships\MedicalRecordItemLoggerRelationships;
use App\User;

abstract class MedicalRecordEloquent extends \App\BaseModel implements MedicalRecord
{
    use MedicalRecordItemLoggerRelationships;

    /**
     * @var integer;
     */
    protected $billingProviderIdPrediction;

    /**
     * @var int
     */
    protected $locationIdPrediction;

    /**
     * @var int
     */
    protected $practiceIdPrediction;

    /**
     * @return MedicalRecord
     */
    public function createImportedMedicalRecord(): MedicalRecord
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
     * Log the data into MedicalRecordSectionLogs, so that they can be fed to the Importer.
     *
     * @return MedicalRecord
     */
    public function createLogs(): MedicalRecord
    {
        $this->getLogger()->logAllSections();

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
     * @return mixed
     */
    public function getLocationIdPrediction()
    {
        return $this->locationIdPrediction;
    }

    /**
     * @return mixed
     */
    public function getPracticeIdPrediction()
    {
        return $this->practiceIdPrediction;
    }

    /**
     * Handles importing a MedicalRecord for QA.
     *
     * @return ImportedMedicalRecord
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
            ->importProviders()
            ->raiseConcerns();

        return $this->importedMedicalRecord;
    }

    /**
     * Import Allergies for QA.
     *
     * @return MedicalRecord
     */
    public function importAllergies(): MedicalRecord
    {
        $importer = new Allergies();
        $importer->import($this->id, get_class($this), $this->importedMedicalRecord);

        return $this;
    }

    /**
     * Import Demographics for QA.
     *
     * @return \App\Contracts\Importer\MedicalRecord\MedicalRecord
     */
    public function importDemographics(): MedicalRecord
    {
        $importer = new Demographics();
        $importer->import($this->id, get_class($this), $this->importedMedicalRecord);

        return $this;
    }

    /**
     * Import Document for QA.
     *
     * @return \App\Contracts\Importer\MedicalRecord\MedicalRecord
     */
    public function importDocument(): MedicalRecord
    {
        return $this;
    }

    /**
     * @return mixed
     */
    abstract public function importedMedicalRecord();

    /**
     * Import Insurance Policies for QA.
     *
     * @return MedicalRecord
     */
    public function importInsurance(): MedicalRecord
    {
        $importer = new Insurance();
        $importer->import($this->id, get_class($this), $this->importedMedicalRecord);

        return $this;
    }

    /**
     * Import Medications for QA.
     *
     * @return \App\Contracts\Importer\MedicalRecord\MedicalRecord
     */
    public function importMedications(): MedicalRecord
    {
        $importer = new Medications($this->id, get_class($this), $this->importedMedicalRecord);
        $importer->import($this->id, get_class($this), $this->importedMedicalRecord);

        return $this;
    }

    /**
     * Import Problems for QA.
     *
     * @return \App\Contracts\Importer\MedicalRecord\MedicalRecord
     */
    public function importProblems(): MedicalRecord
    {
        $importer = new Problems();
        $importer->import($this->id, get_class($this), $this->importedMedicalRecord);

        return $this;
    }

    /**
     * Import Providers for QA.
     *
     * @return \App\Contracts\Importer\MedicalRecord\MedicalRecord
     */
    public function importProviders(): MedicalRecord
    {
        return $this;
    }

    /**
     * Predict which BillingProvider should be attached to this MedicalRecord.
     *
     * @return MedicalRecord
     */
    public function predictBillingProvider(): MedicalRecord
    {
        if ($this->billing_provider_id) {
            $this->setBillingProviderIdPrediction($this->billing_provider_id);

            return $this;
        }

        if ($this->getBillingProviderIdPrediction()) {
            return $this;
        }

        //historic lookup
        $historicPredictor  = new HistoricBillingProviderPredictor($this->getDocumentCustodian(), $this->providers);
        $historicPrediction = $historicPredictor->predict();

        if ($historicPrediction) {
            $this->setBillingProviderIdPrediction($historicPrediction);

            return $this;
        }

        return $this;
    }

    /**
     * Predict which Location should be attached to this MedicalRecord.
     *
     * @return MedicalRecord
     */
    public function predictLocation(): MedicalRecord
    {
        if ($this->getLocationIdPrediction()) {
            return $this;
        }

        if (!empty($this->location_id)) {
            $this->setLocationIdPrediction($this->location_id);

            return $this;
        }

        //historic lookup
        $historicPredictor  = new HistoricLocationPredictor($this->getDocumentCustodian(), $this->providers);
        $historicPrediction = $historicPredictor->predict();

        if ($this->getPracticeIdPrediction()) {
            $practice = Practice::find($this->getPracticeIdPrediction());

            $this->setLocationIdPrediction($practice->primary_location_id);
        }

        if ($historicPrediction) {
            if (isset($practice) && !$practice->locations->pluck('id')->contains($historicPrediction)) {
                !$practice->primary_location_id
                    ?: $this->setLocationIdPrediction($practice->primary_location_id);

                return $this;
            }
            $this->setLocationIdPrediction($historicPrediction);
        }

        return $this;
    }

    /**
     * Predict which Practice should be attached to this MedicalRecord.
     *
     * @return MedicalRecord
     */
    public function predictPractice(): MedicalRecord
    {
        if ($this->practice_id) {
            $this->setPracticeIdPrediction($this->practice_id);

            return $this;
        }

        if ($this->getPracticeIdPrediction()) {
            return $this;
        }

        //historic lookup
        $historicPredictor  = new HistoricPracticePredictor($this->getDocumentCustodian(), $this->providers);
        $historicPrediction = $historicPredictor->predict();

        if ($historicPrediction) {
            $this->setPracticeIdPrediction($historicPrediction);

            return $this;
        }

        return $this;
    }

    public function raiseConcerns()
    {
        $demos = $this->demographics()->first();

        if ($demos) {
            $practiceId = optional($demos->ccda)->practice_id;

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
                $this->importedMedicalRecord->duplicate_id = $user->id;
                $this->importedMedicalRecord->save();

                return true;
            }

            $patient = Patient::whereHas('user', function ($q) use ($practiceId) {
                $q->where('program_id', $practiceId);
            })->whereMrnNumber($demos->mrn_number)->first();

            if ($patient) {
                $this->importedMedicalRecord->duplicate_id = $patient->user_id;
                $this->importedMedicalRecord->save();

                return true;
            }
        }
    }

    /**
     * @param mixed $billingProviderId
     *
     * @return MedicalRecord
     */
    public function setBillingProviderIdPrediction($billingProviderId): MedicalRecord
    {
        $this->billingProviderIdPrediction = $billingProviderId;

        return $this;
    }

    /**
     * @param mixed $locationId
     *
     * @return MedicalRecord
     */
    public function setLocationIdPrediction($locationId): MedicalRecord
    {
        $this->locationIdPrediction = $locationId;

        return $this;
    }

    /**
     * @param mixed $practiceId
     *
     * @return MedicalRecord
     */
    public function setPracticeIdPrediction($practiceId): MedicalRecord
    {
        $this->practiceIdPrediction = $practiceId;

        return $this;
    }
}
