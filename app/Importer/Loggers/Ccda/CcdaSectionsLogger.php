<?php

namespace App\Importer\Loggers\Ccda;


use App\CLH\Repositories\CCDImporterRepository;
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
        $this->ccd = $ccd->json
            ? json_decode($ccd->json)
            : json_decode((new CCDImporterRepository())->toJson($ccd->xml));

        $this->ccdaId = $ccd->id;
        $this->vendorId = $ccd->vendor_id;

        $this->foreignKeys = [
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

        $data = $this->transformer->document($document);

        $shouldBeIgnored = DocumentLog::where('ml_ignore', '=', 1)
            ->where('custodian', $data['custodian'])
            ->first();

        if ($shouldBeIgnored) {
            return $this;
        }


        $saved = DocumentLog::create(
            array_merge($data, $this->foreignKeys)
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
            $data = $this->transformer->provider($provider);

            $shouldBeIgnored = ProviderLog::where('ml_ignore', '=', 1)
                ->where('street', $data['street'])
                ->where('city', $data['city'])
                ->where('state', $data['state'])
                ->where('zip', $data['zip'])
                ->where('cell_phone', $data['cell_phone'])
                ->where('home_phone', $data['home_phone'])
                ->where('work_phone', $data['work_phone'])
                ->first();

            if ($shouldBeIgnored) {
                continue;
            }

            $saved = ProviderLog::create(
                array_merge($data, $this->foreignKeys)
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