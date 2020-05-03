<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecord\Templates;

use Carbon\Carbon;
use CircleLinkHealth\Eligibility\MedicalRecord\ValueObjects\Problem;

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

        return collect(collect(json_decode($this->data['allergies_string']))->first())
            ->map(
                function ($allergy) {
                    if ( ! validAllergyName($this->getAllergyName($allergy))) {
                        return false;
                    }

                    return [
                        'date_range' => [
                            'start' => '',
                            'end'   => null,
                        ],
                        'name'             => null,
                        'code'             => '',
                        'code_system'      => '',
                        'code_system_name' => '',
                        'status'           => null,
                        'severity'         => '',
                        'reaction'         => [
                            'name'        => '',
                            'code'        => '',
                            'code_system' => '',
                        ],
                        'reaction_type' => [
                            'name'             => '',
                            'code'             => '',
                            'code_system'      => '',
                            'code_system_name' => '',
                        ],
                        'allergen' => [
                            'name'             => $this->getAllergyName($allergy),
                            'code'             => '',
                            'code_system'      => '',
                            'code_system_name' => '',
                        ],
                    ];
                }
            )
            ->filter()
            ->values()
            ->toArray();
    }

    public function fillDemographicsSection(): object
    {
        return (object) [
            'ids' => [
                'mrn_number' => $this->getMrn(),
            ],
            'name' => [
                'prefix' => null,
                'given'  => [
                    $this->getFirstName(),
                ],
                'family' => $this->getLastName(),
                'suffix' => null,
            ],
            'dob'            => $this->getDob()->toDateString(),
            'gender'         => $this->data['gender'],
            'mrn_number'     => $this->getMrn(),
            'marital_status' => '',
            'address'        => [
                'street' => [
                    $this->getAddressLine1(),
                    $this->getAddressLine2(),
                ],
                'city'    => $this->data['city'],
                'state'   => $this->data['state'],
                'zip'     => $this->getZipCode(),
                'country' => '',
            ],
            'phones' => [
                0 => [
                    'type'   => 'home',
                    'number' => $this->data['home_phone'] ?? '',
                ],
                1 => [
                    'type'   => 'primary_phone',
                    'number' => $this->data['primary_phone'] ?? '',
                ],
                2 => [
                    'type'   => 'mobile',
                    'number' => $this->data['cell_phone'] ?? '',
                ],
            ],
            'email'      => null,
            'language'   => null,
            'race'       => null,
            'ethnicity'  => null,
            'religion'   => null,
            'birthplace' => [
                'state'   => null,
                'zip'     => null,
                'country' => null,
            ],
            'guardian' => [
                'name' => [
                    'given' => [
                    ],
                    'family' => null,
                ],
                'relationship'      => null,
                'relationship_code' => null,
                'address'           => [
                    'street' => [
                    ],
                    'city'    => null,
                    'state'   => null,
                    'zip'     => null,
                    'country' => null,
                ],
                'phone' => [
                    'home' => null,
                ],
            ],
            'patient_contacts' => [
            ],
            'provider' => [
                'ids' => [
                ],
                'organization' => null,
                'phones'       => [
                ],
                'address' => [
                    'street' => [
                    ],
                    'city'    => null,
                    'state'   => null,
                    'zip'     => null,
                    'country' => null,
                ],
            ],
        ];
    }

    public function fillDocumentSection(): object
    {
        return (object) [
            'custodian' => [
                'name' => $this->getProviderName(),
            ],
            'date'   => '',
            'title'  => '',
            'author' => [
                'npi'  => '',
                'name' => [
                    'prefix' => null,
                    'given'  => [],
                    'family' => null,
                    'suffix' => null,
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
                'phones' => [
                    0 => [
                        'type'   => '',
                        'number' => '',
                    ],
                ],
            ],
            'documentation_of' => [
                0 => [
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
            ],
            'legal_authenticator' => [
                'date'            => null,
                'ids'             => [],
                'assigned_person' => [
                    'prefix' => null,
                    'given'  => [],
                    'family' => null,
                    'suffix' => null,
                ],
                'representedOrganization' => [
                    'ids'     => [],
                    'name'    => null,
                    'phones'  => [],
                    'address' => [
                        'street'  => [],
                        'city'    => null,
                        'state'   => null,
                        'zip'     => null,
                        'country' => null,
                    ],
                ],
            ],
            'location' => [
                'name'    => null,
                'address' => [
                    'street'  => [],
                    'city'    => null,
                    'state'   => null,
                    'zip'     => null,
                    'country' => null,
                ],
                'encounter_date' => null,
            ],
        ];
    }

    public function fillMedicationsSection(): array
    {
        if ( ! array_key_exists('medications_string', $this->data)) {
            return [];
        }

        return collect(collect(json_decode($this->data['medications_string']))->first())
            ->map(
                function ($medication) {
                    return [
                        'reference'       => null,
                        'reference_title' => null,
                        'reference_sig'   => null,
                        'date_range'      => [
                            'start' => $medication->StartDate ?? null,
                            'end'   => $medication->StopDate ?? null,
                        ],
                        'status'  => $medication->Status,
                        'text'    => null,
                        'product' => [
                            'name'        => $medication->Name,
                            'code'        => '',
                            'code_system' => '',
                            'text'        => $medication->Sig,
                            'translation' => [
                                'name'             => null,
                                'code'             => null,
                                'code_system'      => null,
                                'code_system_name' => null,
                            ],
                        ],
                        'dose_quantity' => [
                            'value' => null,
                            'unit'  => null,
                        ],
                        'rate_quantity' => [
                            'value' => null,
                            'unit'  => null,
                        ],
                        'precondition' => [
                            'name'        => null,
                            'code'        => null,
                            'code_system' => null,
                        ],
                        'reason' => [
                            'name'        => null,
                            'code'        => null,
                            'code_system' => null,
                        ],
                        'route' => [
                            'name'             => null,
                            'code'             => null,
                            'code_system'      => null,
                            'code_system_name' => null,
                        ],
                        'schedule' => [
                            'type'         => null,
                            'period_value' => null,
                            'period_unit'  => null,
                        ],
                        'vehicle' => [
                            'name'             => null,
                            'code'             => null,
                            'code_system'      => null,
                            'code_system_name' => null,
                        ],
                        'administration' => [
                            'name'             => null,
                            'code'             => null,
                            'code_system'      => null,
                            'code_system_name' => null,
                        ],
                        'prescriber' => [
                            'organization' => null,
                            'person'       => null,
                        ],
                    ];
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

        return collect(collect(json_decode($this->data['problems_string']))->first())
            ->map(
                function ($problem) {
                    if ( ! validProblemName($problem->Name)) {
                        return false;
                    }

                    return (new Problem())
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
        return $this->data['first_name'];
    }

    public function getLastName(): string
    {
        return $this->data['last_name'];
    }

    public function getMrn(): string
    {
        return $this->data['mrn'] ?? $this->data['mrn_number'] ?? $this->data['patient_id'];
    }

    public function getProviderName(): string
    {
        return $this->data['referring_provider_name'];
    }

    public function getType(): string
    {
        return 'csv-with-json';
    }

    private function getAddressLine1(): string
    {
        return $this->data['street'];
    }

    private function getAddressLine2(): string
    {
        return $this->data['street2'] ?? '';
    }

    private function getAllergyName($allergy): string
    {
        return $allergy->Name;
    }

    private function getZipCode(): string
    {
        return $this->data['zip'];
    }
}
