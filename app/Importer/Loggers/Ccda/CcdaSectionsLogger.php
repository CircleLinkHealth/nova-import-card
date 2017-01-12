<?php

namespace App\Importer\Loggers\Ccda;


use App\Contracts\Importer\MedicalRecord\MedicalRecordLogger;
use App\Importer\Models\ItemLogs\AllergyLog;
use App\Importer\Models\ItemLogs\DemographicsLog;
use App\Importer\Models\ItemLogs\DocumentLog;
use App\Importer\Models\ItemLogs\InsuranceLog;
use App\Importer\Models\ItemLogs\MedicationLog;
use App\Importer\Models\ItemLogs\ProblemLog;
use App\Importer\Models\ItemLogs\ProviderLog;
use App\Models\MedicalRecords\Ccda;

class CcdaSectionsLogger implements MedicalRecordLogger
{
    protected $ccd;
    protected $vendorId;

    protected $transformer;

    protected $foreignKeys = [];


    public function __construct(Ccda $ccd)
    {
        $this->ccd = json_decode($ccd->json);
        $this->ccdaId = $ccd->id;
        $this->vendorId = $ccd->vendor_id;

        $this->foreignKeys = [
            'ccda_id'             => $this->ccdaId,
            'vendor_id'           => $this->vendorId,
            'medical_record_type' => Ccda::class,
            'medical_record_id'   => $this->ccdaId,
        ];

        $this->transformer = new CcdToLogTranformer();
    }

    /**
     * Transform the Demographics Section into Log models..
     * @return MedicalRecordLogger
     */
    public function logDemographicsSection() : MedicalRecordLogger
    {
        $demographics = $this->ccd->demographics;

        $saved = DemographicsLog::create(
            array_merge($this->transformer->demographics($demographics), $this->foreignKeys)
        );

        return $this;
    }

    /**
     * Transform the Document Section into Log models..
     * @return MedicalRecordLogger
     */
    public function logDocumentSection() : MedicalRecordLogger
    {
        $document = $this->ccd->document;

        $saved = DocumentLog::create(
            array_merge($this->transformer->document($document), $this->foreignKeys)
        );

        return $this;
    }

    /**
     * Transform the Medications Section into Log models..
     * @return MedicalRecordLogger
     */
    public function logMedicationsSection() : MedicalRecordLogger
    {
        $medications = $this->ccd->medications;

        foreach ($medications as $med) {
            $saved = MedicationLog::create(
                array_merge($this->transformer->medication($med), $this->foreignKeys)
            );
        }

        return $this;
    }

    /**
     * Transform the Problems Section into Log models..
     * @return MedicalRecordLogger
     */
    public function logProblemsSection() : MedicalRecordLogger
    {
        $problems = $this->ccd->problems;

        foreach ($problems as $prob) {
            $saved = ProblemLog::create(
                array_merge($this->transformer->problem($prob), $this->foreignKeys)
            );
        }

        return $this;
    }

    /**
     * Transform the Providers Section into Log models..
     * @return MedicalRecordLogger
     */
    public function logProvidersSection() : MedicalRecordLogger
    {
        //Add them both together
        array_push($this->ccd->document->documentation_of, $this->ccd->document->author);

        array_push($this->ccd->document->documentation_of, $this->ccd->demographics->provider);

        $providers = $this->ccd->document->documentation_of;

        foreach ($providers as $provider) {
            $saved = ProviderLog::create(
                array_merge($this->transformer->provider($provider), $this->foreignKeys)
            );
        }

        return $this;
    }

    /**
     * Log all Sections.
     */
    public function logAllSections()
    {
        $this->logAllergiesSection()
            ->logDemographicsSection()
            ->logDocumentSection()
            ->logInsuranceSection()
            ->logMedicationsSection()
            ->logProblemsSection()
            ->logProvidersSection();
    }

    /**
     * Transform the Allergies Section into Log models..
     * @return MedicalRecordLogger
     */
    public function logAllergiesSection() : MedicalRecordLogger
    {
        $allergies = $this->ccd->allergies;

        foreach ($allergies as $allergy) {
            $saved = AllergyLog::create(
                array_merge($this->transformer->allergy($allergy), $this->foreignKeys)
            );
        }

        return $this;
    }

    /**
     * Log Insurance Section.
     * @return MedicalRecordLogger
     */
    public function logInsuranceSection() : MedicalRecordLogger
    {
        if (!empty($this->ccd->payers)) {
            foreach ($this->ccd->payers as $payer) {

                if (empty($payer->insurance)) {
                    continue;
                }

                $insurance = InsuranceLog::create([
                    'medical_record_id'   => $this->ccdaId,
                    'medical_record_type' => Ccda::class,
                    'name'                => $payer->insurance,
                    'type'                => $payer->policy_type,
                    'policy_id'           => $payer->policy_id,
                    'relation'            => $payer->relation,
                    'subscriber'          => $payer->subscriber,
                    'approved'            => false,
                    'import'              => true,
                ]);
            }
        }

        return $this;
    }
}