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

use Illuminate\Support\Str;
use App\Console\Commands\OverwriteNBIImportedData;
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
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Collection;

abstract class MedicalRecordEloquent extends \CircleLinkHealth\Core\Entities\BaseModel implements MedicalRecord
{
    use MedicalRecordItemLoggerRelationships;

    /**
     * @var integer;
     */
    protected $billingProviderIdPrediction;

    /**
     * @var ImportedMedicalRecord
     */
    protected $importedMedicalRecord;

    /**
     * A collection.
     *
     * @var Collection
     */
    protected $insurances;

    /**
     * @var int
     */
    protected $locationIdPrediction;

    /**
     * @var int
     */
    protected $practiceIdPrediction;

    /**
     * A collection of the patient's problems () separated in groups (monitored, not_monitored, do_not_import).
     *
     * @var Collection
     */
    protected $problemsInGroups;

    public function createImportedMedicalRecord(): MedicalRecord
    {
        $this->importedMedicalRecord = ImportedMedicalRecord::create(
            [
                'medical_record_type' => get_class($this),
                'medical_record_id'   => $this->id,
                'billing_provider_id' => $this->getBillingProviderIdPrediction(),
                'location_id'         => $this->getLocationIdPrediction(),
                'practice_id'         => $this->getPracticeIdPrediction(),
            ]
        );

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

    public function getId(): ?int
    {
        return $this->id ?? null;
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

    public function getType(): ?string
    {
        return get_class($this);
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
        $importer         = new Insurance();
        $this->insurances = $importer->import($this->id, get_class($this), $this->importedMedicalRecord);

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
        $importer               = new Problems();
        $this->problemsInGroups = $importer->import($this->id, get_class($this), $this->importedMedicalRecord);

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

        if ( ! empty($this->location_id)) {
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
            if (isset($practice) && ! $practice->locations->pluck('id')->contains($historicPrediction)) {
                ! $practice->primary_location_id
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
        $isDuplicate              = $this->isDuplicate();
        $hasAtLeast2CcmConditions = $this->hasAtLeast2CcmConditions();
        $hasAtLeast1BhiCondition  = $this->hasAtLeast1BhiCondition();
        $hasMedicare              = $this->hasMedicare();
        $wasNBIOverwritten        = app(OverwriteNBIImportedData::class)->lookupAndReplacePatientData($this->importedMedicalRecord);

        $this->importedMedicalRecord->validation_checks = [
            ImportedMedicalRecord::CHECK_HAS_AT_LEAST_2_CCM_CONDITIONS => $hasAtLeast2CcmConditions,
            ImportedMedicalRecord::CHECK_HAS_AT_LEAST_1_BHI_CONDITION  => $hasAtLeast1BhiCondition,
            ImportedMedicalRecord::CHECK_HAS_MEDICARE                  => $hasMedicare,
            ImportedMedicalRecord::WAS_NBI_OVERWRITTEN                 => $wasNBIOverwritten,
        ];

        $this->importedMedicalRecord->save();
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

    /**
     * @return bool
     */
    private function hasAtLeast1BhiCondition()
    {
        return $this->problemsInGroups->get('monitored', collect())
            ->unique(
                function ($p) {
                    return $p['attributes']['cpm_problem_id'];
                }
                                      )
            ->where('is_behavioral', true)
            ->count() >= 1;
    }

    /**
     * @return bool
     */
    private function hasAtLeast2CcmConditions()
    {
        return $this->problemsInGroups->get('monitored', collect())
            ->unique(
                function ($p) {
                    return $p['attributes']['cpm_problem_id'];
                }
                                      )
            ->count() >= 2;
    }

    /**
     * @return bool
     */
    private function hasMedicare()
    {
        return $this->insurances->reject(
            function ($i) {
                return ! Str::contains(strtolower($i->name.$i->type), 'medicare');
            }
            )
            ->count() >= 1;
    }

    /**
     * Checks whether the patient we have just imported exists in the system.
     *
     * @return bool|null
     */
    private function isDuplicate()
    {
        $demos = $this->demographics()->first();

        if ( ! $demos) {
            return null;
        }

        $practiceId = optional($demos->ccda)->practice_id;

        $query = User::whereFirstName($demos->first_name)
            ->whereLastName($demos->last_name)
            ->whereHas(
                'patientInfo',
                function ($q) use ($demos) {
                    $q->whereBirthDate($demos->dob);
                }
                     );
        if ($practiceId) {
            $query = $query->where('program_id', $practiceId);
        }

        $user = $query->first();

        if ($user) {
            $this->importedMedicalRecord->duplicate_id = $user->id;
            $this->importedMedicalRecord->save();

            return true;
        }

        $patient = Patient::whereHas(
            'user',
            function ($q) use ($practiceId) {
                $q->where('program_id', $practiceId);
            }
        )->whereMrnNumber($demos->mrn_number)->first();

        if ($patient) {
            $this->importedMedicalRecord->duplicate_id = $patient->user_id;
            $this->importedMedicalRecord->save();

            return true;
        }

        return false;
    }
}
