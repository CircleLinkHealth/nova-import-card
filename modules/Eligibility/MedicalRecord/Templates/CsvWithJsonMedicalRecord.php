<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecord\Templates;

use Carbon\Carbon;
use CircleLinkHealth\Core\Utilities\JsonFixer;
use CircleLinkHealth\Eligibility\MedicalRecord\Templates\Resources\Address;
use CircleLinkHealth\Eligibility\MedicalRecord\Templates\Resources\Allergy as AllergyResource;
use CircleLinkHealth\Eligibility\MedicalRecord\Templates\Resources\Demographics as DemographicsResource;
use CircleLinkHealth\Eligibility\MedicalRecord\Templates\Resources\Document;
use CircleLinkHealth\Eligibility\MedicalRecord\Templates\Resources\Medication;
use CircleLinkHealth\Eligibility\MedicalRecord\Templates\Resources\PersonName;
use CircleLinkHealth\Eligibility\MedicalRecord\Templates\Resources\Problem as ProblemResource;
use CircleLinkHealth\SharedModels\Entities\Ccda;

class CsvWithJsonMedicalRecord extends BaseMedicalRecordTemplate
{
    /**
     * @var array
     */
    protected $data;

    public function __construct(array $medicalRecord)
    {
        $this->data = sanitize_array_keys($medicalRecord);
    }

    public function fillAllergiesSection(): array
    {
        if ( ! array_key_exists('allergies_string', $this->data)) {
            return [];
        }

        $decoded = json_decode($this->data['allergies_string']);

        if (is_null($decoded)) {
            $decoded = json_decode(JsonFixer::attemptFix($this->data['allergies_string']));
        }

        return collect(collect($decoded)->first())
            ->map(
                function ($allergy) {
                    if ( ! validAllergyName($this->getAllergyName($allergy))) {
                        return false;
                    }

                    $allergyResource = new AllergyResource();
                    $allergyResource->allergenName = $this->getAllergyName($allergy);

                    return $allergyResource->toArray();
                }
            )
            ->filter()
            ->values()
            ->toArray();
    }

    public function fillDemographicsSection(): array
    {
        $demographicsResource            = new DemographicsResource();
        $demographicsResource->mrnNumber = $this->getMrn();

        $patientName        = new PersonName();
        $patientName->given = [
            $this->getFirstName(),
        ];
        $patientName->family = $this->getLastName();

        $demographicsResource->patientName = $patientName;
        $demographicsResource->dob         = $this->getDob()->toDateString();
        $demographicsResource->gender      = $this->data['gender'] ?? null;
        $demographicsResource->mrn_number  = $this->getMrn();

        $patientAddress         = new Address();
        $patientAddress->street = [
            $this->getAddressLine1(),
            $this->getAddressLine2(),
        ];
        $patientAddress->city  = $this->data['city'];
        $patientAddress->state = $this->data['state'];
        $patientAddress->zip   = $this->getZipCode();

        $demographicsResource->patientAddress      = $patientAddress;
        $demographicsResource->patientHomePhone    = $this->data['home_phone'] ?? '';
        $demographicsResource->patientPrimaryPhone = $this->data['primary_phone'] ?? '';
        $demographicsResource->patientMobilePhone  = $this->data['cell_phone'] ?? '';

        return $demographicsResource->toArray();
    }

    public function fillDocumentSection(): array
    {
        $documentResource = new Document();
        $documentResource->custodianName = $this->getProviderName();
        $documentResource->documentationOfName = $this->getProviderName();
        $documentResource->locationName = $this->data['cpm_location_name'] ?? null;
        
        return $documentResource->toArray();
    }

    public function fillEncountersSection(): array
    {
        return [];
    }

    public function fillMedicationsSection(): array
    {
        if ( ! array_key_exists('medications_string', $this->data)) {
            return [];
        }

        $decoded = json_decode($this->data['medications_string']);

        if (is_null($decoded)) {
            $decoded = json_decode(JsonFixer::attemptFix($this->data['medications_string']));
        }

        return collect(collect($decoded)->first())
            ->map(
                function ($medication) {
                    if ( ! isset($medication->Name)) {
                        return false;
                    }
                    
                    $medicationResource = new Medication();
                    $medicationResource->dateRangeStart = $medication->StartDate ?? null;
                    $medicationResource->dateRangeEnd = $medication->StopDate ?? null;
                    $medicationResource->status = $medication->Status ?? null;
                    $medicationResource->productName = $medication->Name;
                    $medicationResource->productText = $medication->Sig ?? null;

                    return $medicationResource->toArray();
                }
            )
            ->filter()
            ->values()
            ->toArray();
    }

    public function fillPayersSection(): array
    {
        $insurances = [
            'primary_insurance'   => $this->data['primary_insurance'] ?? null,
            'secondary_insurance' => $this->data['secondary_insurance'] ?? null,
            'tertiary_insurance'  => $this->data['tertiary_insurance'] ?? null,
        ];

        return collect($insurances)
            ->filter()
            ->map(
                function ($insurance, $type) {
                    if (empty($insurance)) {
                        return false;
                    }

                    return [
                        'insurance'   => $insurance,
                        'policy_type' => $type,
                        'policy_id'   => null,
                        'relation'    => null,
                        'subscriber'  => null,
                    ];
                }
            )
            ->filter()
            ->values()
            ->toArray();
    }

    public function fillProblemsSection(): array
    {
        if ( ! array_key_exists('problems_string', $this->data)) {
            return [];
        }

        $decoded = json_decode($this->data['problems_string']);

        if (is_null($decoded)) {
            $decoded = json_decode(JsonFixer::attemptFix($this->data['problems_string']));
        }

        return collect(collect($decoded)->first())
            ->map(
                function ($problem) {
                    if ( ! validProblemName($problem->Name)) {
                        return false;
                    }

                    if ( ! isset($problem->Name, $problem->AddedDate, $problem->ResolveDate, $problem->Code, $problem->CodeType)) {
                        return false;
                    }

                    return (new ProblemResource())
                        ->setName($problem->Name)
                        ->setStartDate($problem->AddedDate)
                        ->setEndDate($problem->ResolveDate)
                        ->setCode($problem->Code)
                        ->setCodeSystemName($problem->CodeType)
                        ->toObject();
                }
            )
            ->filter()
            ->values()
            ->toArray();
    }

    public function fillVitals(): array
    {
        return [
            [
                'date'    => null,
                'results' => [
                    [
                        'name'             => null,
                        'code'             => null,
                        'code_system'      => null,
                        'code_system_name' => null,
                        'value'            => null,
                        'unit'             => null,
                    ],
                ],
            ],
        ];
    }

    public function getDob(): Carbon
    {
        return Carbon::parse($this->data['dob']);
    }

    public function getFirstName(): string
    {
        return $this->data['first_name'] ?? '';
    }

    public function getLastName(): string
    {
        return $this->data['last_name'] ?? '';
    }

    public function getMrn(): string
    {
        return $this->data['mrn'] ?? $this->data['mrn_number'] ?? $this->data['patient_id'] ?? '';
    }

    public function getProviderName(): string
    {
        return $this->data['referring_provider_name'] ?? $this->data['preferred_provider'] ?? $this->data['provider'] ?? $this->data['provider_name'] ?? '';
    }

    public function getType(): string
    {
        return Ccda::CSV_WITH_JSON;
    }

    private function getAddressLine1(): string
    {
        return $this->data['street'] ?? '';
    }

    private function getAddressLine2(): string
    {
        return $this->data['street2'] ?? '';
    }

    private function getAllergyName($allergy): string
    {
        return $allergy->Name ?? $allergy->name ?? '';
    }

    private function getZipCode(): string
    {
        return $this->data['zip'] ?? '';
    }
}
