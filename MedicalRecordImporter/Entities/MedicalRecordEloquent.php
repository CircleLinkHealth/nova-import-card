<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecordImporter\Entities;

/*
 * Created by PhpStorm.
 * User: michalis
 * Date: 16/01/2017
 * Time: 4:05 PM
 */

use App\Console\Commands\OverwriteNBIImportedData;
use App\Search\ProviderByName;
use App\Traits\Relationships\MedicalRecordItemLoggerRelationships;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\MedicalRecord;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Events\CcdaImported;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Predictors\HistoricBillingProviderPredictor;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Predictors\HistoricLocationPredictor;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Predictors\HistoricPracticePredictor;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Sections\Allergies;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Sections\Demographics;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Sections\Insurance;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Sections\Medications;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Sections\Problems;
use Illuminate\Support\Collection;

abstract class MedicalRecordEloquent extends \CircleLinkHealth\Core\Entities\BaseModel implements MedicalRecord
{
    use MedicalRecordItemLoggerRelationships;

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
     * A collection of the patient's problems () separated in groups (monitored, not_monitored, do_not_import).
     *
     * @var Collection
     */
    protected $problemsInGroups;

    /**
     * Log the data into MedicalRecordSectionLogs, so that they can be fed to the Importer.
     */
    public function createLogs(): MedicalRecord
    {
        $this->getLogger()->logAllSections();

        return $this;
    }
    
    public function scopeHasUPG0506PdfCareplanMedia($query)
    {
        return $query->whereExists(function ($query) {
            $query->select('id')
                  ->from('media')
                  ->where('custom_properties->is_pdf', 'true')->where('custom_properties->is_upg0506', 'true')->where('custom_properties->care_plan->demographics->mrn_number', (string)$this->mrn);
        });
    }

    /**
     * @return mixed
     */
    public function getBillingProviderId(): ?int
    {
        return $this->billing_provider_id;
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    /**
     * @return mixed
     */
    public function getLocationId(): ?int
    {
        return $this->location_id;
    }

    /**
     * @return mixed
     */
    public function getPracticeId(): ?int
    {
        return $this->practice_id;
    }

    abstract public function getReferringProviderName();

    public function getType(): ?string
    {
        return get_class($this);
    }

    public function guessPracticeLocationProvider(): MedicalRecord
    {
        if ($term = $this->getReferringProviderName()) {
            $this->setAllPracticeInfoFromProvider($term);
        }

        if ( ! $this->getPracticeId()) {
            $this->predictPractice();
        }

        if ( ! $this->getLocationId()) {
            $this->predictLocation();
        }

        if ( ! $this->getBillingProviderId()) {
            $this->predictBillingProvider();
        }

        if ($this->isDirty()) {
            $this->save();
        }

        return $this;
    }

    /**
     * Handles importing a MedicalRecordForEligibilityCheck for QA.
     *
     * @return ImportedMedicalRecord
     */
    public function import()
    {
        $this->createLogs()
            ->guessPracticeLocationProvider()
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
     */
    public function importAllergies(): MedicalRecord
    {
        $importer = new Allergies();
        $importer->import($this->id, get_class($this), $this->importedMedicalRecord);

        return $this;
    }

    /**
     * Import Demographics for QA.
     */
    public function importDemographics(): MedicalRecord
    {
        $importer = new Demographics();
        $importer->import($this->id, get_class($this), $this->importedMedicalRecord);

        return $this;
    }

    /**
     * Import Document for QA.
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
     */
    public function importInsurance(): MedicalRecord
    {
        $importer         = new Insurance();
        $this->insurances = $importer->import($this->id, get_class($this), $this->importedMedicalRecord);

        return $this;
    }

    /**
     * Import Medications for QA.
     */
    public function importMedications(): MedicalRecord
    {
        $importer = new Medications($this->id, get_class($this), $this->importedMedicalRecord);
        $importer->import($this->id, get_class($this), $this->importedMedicalRecord);

        return $this;
    }

    /**
     * Import Problems for QA.
     */
    public function importProblems(): MedicalRecord
    {
        $importer               = new Problems();
        $this->problemsInGroups = $importer->import($this->id, get_class($this), $this->importedMedicalRecord);

        return $this;
    }

    /**
     * Import Providers for QA.
     */
    public function importProviders(): MedicalRecord
    {
        return $this;
    }

    /**
     * Predict which BillingProvider should be attached to this MedicalRecordForEligibilityCheck.
     */
    public function predictBillingProvider(): MedicalRecord
    {
        if ($this->getBillingProviderId()) {
            return $this;
        }

        //historic lookup
        $historicPredictor  = new HistoricBillingProviderPredictor($this->getDocumentCustodian(), $this->providers);
        $historicPrediction = $historicPredictor->predict();

        if ($historicPrediction) {
            $this->setBillingProviderId($historicPrediction);
        }

        return $this;
    }

    /**
     * Predict which Location should be attached to this MedicalRecordForEligibilityCheck.
     */
    public function predictLocation(): MedicalRecord
    {
        if ($this->getLocationId()) {
            return $this;
        }

        //historic lookup
        $historicPredictor  = new HistoricLocationPredictor($this->getDocumentCustodian(), $this->providers);
        $historicPrediction = $historicPredictor->predict();

        if ($this->getPracticeId()) {
            $practice = Practice::find($this->getPracticeId());

            $this->setLocationId($practice->primary_location_id);
        }

        if ($historicPrediction) {
            if (isset($practice) && ! $practice->locations->pluck('id')->contains(
                $historicPrediction
            ) && $primaryLocationId = $practice->getPrimaryLocationIdAttribute()) {
                $this->setLocationId($primaryLocationId);

                return $this;
            }
            $this->setLocationId($historicPrediction);
        }

        return $this;
    }

    /**
     * Predict which Practice should be attached to this MedicalRecordForEligibilityCheck.
     */
    public function predictPractice(): MedicalRecord
    {
        if ($this->getPracticeId()) {
            return $this;
        }

        //historic lookup
        $historicPredictor  = new HistoricPracticePredictor($this->getDocumentCustodian(), $this->providers);
        $historicPrediction = $historicPredictor->predict();

        if ($historicPrediction) {
            $this->setPracticeId($historicPrediction);
        }

        return $this;
    }

    public function raiseConcerns()
    {
        $isDuplicate              = $this->isDuplicate();
        $hasAtLeast2CcmConditions = $this->hasAtLeast2CcmConditions();
        $hasAtLeast1BhiCondition  = $this->hasAtLeast1BhiCondition();
        $hasMedicare              = $this->hasMedicare();
        $wasNBIOverwritten        = app(OverwriteNBIImportedData::class)->lookupAndReplacePatientData(
            $this->importedMedicalRecord
        );

        $this->importedMedicalRecord->validation_checks = [
            ImportedMedicalRecord::CHECK_HAS_AT_LEAST_2_CCM_CONDITIONS => $hasAtLeast2CcmConditions,
            ImportedMedicalRecord::CHECK_HAS_AT_LEAST_1_BHI_CONDITION  => $hasAtLeast1BhiCondition,
            ImportedMedicalRecord::CHECK_HAS_MEDICARE                  => $hasMedicare,
            ImportedMedicalRecord::WAS_NBI_OVERWRITTEN                 => $wasNBIOverwritten,
        ];

        $this->importedMedicalRecord->save();
    }

    /**
     * Search for a Billing Provider using a search term, and.
     */
    public function searchBillingProvider(string $term): ?User
    {
        $baseQuery = (new ProviderByName())->query($term);

        if ('algolia' === config('scout.driver')) {
            return $baseQuery
                ->with([
                    'typoTolerance' => true,
                ])->when( ! empty($this->practice_id), function ($q) {
                           $q->whereIn('practice_ids', [$this->practice_id]);
                       })
                ->first();
        }

        return $baseQuery->when(
            ! empty($this->practice_id),
            function ($q) {
                $q->ofPractice($this->practice_id);
            }
        )->first();
    }

    /**
     * @param mixed $billingProviderId
     */
    public function setBillingProviderId($billingProviderId): MedicalRecord
    {
        $this->billing_provider_id = $billingProviderId;

        return $this;
    }

    /**
     * @param mixed $locationId
     */
    public function setLocationId($locationId): MedicalRecord
    {
        $this->location_id = $locationId;

        return $this;
    }

    /**
     * @param mixed $practiceId
     */
    public function setPracticeId($practiceId): MedicalRecord
    {
        $this->practice_id = $practiceId;

        return $this;
    }

    protected function createImportedMedicalRecord(): MedicalRecord
    {
        $args = [
            'practice_id'         => $this->getPracticeId(),
            'billing_provider_id' => $this->getBillingProviderId(),
            'location_id'         => $this->getLocationId(),
        ];
        
        $this->importedMedicalRecord = ImportedMedicalRecord::firstOrCreate(
            [
                'medical_record_type' => get_class($this),
                'medical_record_id'   => $this->id,
            ],
            $args
        );
        
        if (! $this->importedMedicalRecord->practice_id) {
            $this->importedMedicalRecord->practice_id = $args['practice_id'];
        }
    
        if (! $this->importedMedicalRecord->billing_provider_id) {
            $this->importedMedicalRecord->billing_provider_id = $args['billing_provider_id'];
        }
    
        if (! $this->importedMedicalRecord->location_id) {
            $this->importedMedicalRecord->location_id = $args['location_id'];
        }
        
        if ($this->importedMedicalRecord->isDirty()) {
            $this->importedMedicalRecord->save();
        }

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
                return ! str_contains(strtolower($i->name.$i->type), 'medicare');
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

    private function setAllPracticeInfoFromProvider(string $term)
    {
        $searchProvider = $this->searchBillingProvider($term);

        if ( ! $searchProvider) {
            return;
        }

        if ( ! $this->getPracticeId()) {
            $this->setPracticeId($searchProvider->program_id);
        }

        $this->setBillingProviderId($searchProvider->id);
        $this->setLocationId(optional($searchProvider->loadMissing('locations')->locations->first())->id);
    }
}
