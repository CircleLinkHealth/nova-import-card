<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecord\Templates;

use Carbon\Carbon;
use CircleLinkHealth\Eligibility\MedicalRecord\ValueObjects\Problem;

class CommonwealthMedicalRecord extends BaseMedicalRecordTemplate
{
    /**
     * @var CcdaMedicalRecord
     */
    protected $ccdaMedicalRecord;
    /**
     * @var array
     */
    private $data;

    public function __construct(array $medicalRecord, CcdaMedicalRecord $ccdaMedicalRecord)
    {
        $this->data              = $medicalRecord;
        $this->ccdaMedicalRecord = $ccdaMedicalRecord;
    }

    public function fillAllergiesSection(): array
    {
        return $this->ccdaMedicalRecord->fillAllergiesSection();
    }

    public function fillDemographicsSection(): object
    {
        return $this->ccdaMedicalRecord->fillDemographicsSection();
    }

    public function fillDocumentSection(): object
    {
        $document = $this->ccdaMedicalRecord->fillDocumentSection();

        $document->custodian->name  = $this->getProviderName();
        $document->documentation_of = [
            [
                'provider_id' => null,
                'name'        => [
                    'prefix' => null,
                    'given'  => [
                        0 => $this->getProviderName(),
                    ],
                    'family' => '',
                    'suffix' => '',
                ],
                'phones' => [
                    0 => [
                        'type'   => '',
                        'number' => '',
                    ],
                ],
                'address' => [
                    'street' => [
                        0 => '',
                    ],
                    'city'    => '',
                    'state'   => '',
                    'zip'     => '',
                    'country' => '',
                ],
            ],
        ];

        return $document;
    }

    public function fillEncountersSection(): array
    {
        return [];
    }

    public function fillMedicationsSection(): array
    {
        return $this->ccdaMedicalRecord->fillMedicationsSection();
    }

    public function fillPayersSection(): array
    {
        return $this->ccdaMedicalRecord->fillPayersSection();
    }

    public function fillProblemsSection(): array
    {
        return collect(array_merge((array) $this->ccdaMedicalRecord->fillProblemsSection(), $this->getMedicalHistory(), (array) $this->data['problems'] ?? []))->unique('name')->transform(function ($problem) {
            $problem = (object) $problem;

            return (new Problem())
                ->setName($problem->name)
                ->setStartDate($problem->start ?? null)
                ->setEndDate($problem->end ?? null)
                ->setCode($problem->code ?? null)
                ->setCodeSystemName($problem->code_system_name ?? null)
                ->toObject();
        })->all();
    }

    public function fillVitals(): array
    {
        return $this->ccdaMedicalRecord->fillVitals();
    }

    public function getDob(): Carbon
    {
        return Carbon::parse($this->data['dob']);
    }

    public function getFirstName(): string
    {
        return $this->data['first_name'];
    }

    public function getLastName(): string
    {
        return $this->data['last_name'];
    }

    public function getMrn(): string
    {
        return $this->data['mrn_number'];
    }

    public function getProviderName(): string
    {
        return $this->data['referring_provider_name'];
    }

    public function getType(): string
    {
        return 'commonwealth-pain-associates-pllc';
    }

    private function getAddressLine1(): string
    {
        return $this->data['street'];
    }

    private function getAddressLine2(): string
    {
        return $this->data['street2'];
    }

    private function getAllergyName($allergy): string
    {
        return $allergy->Name;
    }

    private function getMedicalHistory()
    {
        return collect($this->data['medical_history']['questions'] ?? [])->where('answer', 'Y')->pluck(
            'question'
        )->unique()
            ->map(
                function ($historyItem) {
                    if ( ! validProblemName(
                        $historyItem
                    )) {
                        return false;
                    }

                    return (new Problem())->setName(
                        $historyItem
                    )->toArray();
                }
            )->all();
    }

    private function getZipCode(): string
    {
        return $this->data['zip'];
    }
}
