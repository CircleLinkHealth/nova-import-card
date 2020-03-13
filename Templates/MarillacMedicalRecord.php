<?php

namespace CircleLinkHealth\Eligibility\Templates;

use Carbon\Carbon;

class MarillacMedicalRecord
{
    /**
     * @var array
     */
    private $data;
    
    public function __construct(array $medicalRecord)
    {
        $this->data = $medicalRecord;
    }
    
    public function toArray()
    {
        return [
            'type'         => __CLASS__,
            'document'     => $this->fillDocumentSection(),
            'allergies'    => $this->fillAllergiesSection(),
            'demographics' => $this->fillDemographicsSection(),
            'medications'  => $this->fillMedicationsSection(),
            'payers'       => [
            ],
            'problems'     => $this->fillProblemsSection(),
            'vitals'       => $this->fillVitals(),
        ];
    }
    
    /**
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }
    
    private function fillAllergiesSection()
    {
        return collect(collect(json_decode($this->data['allergies_string']))->first())
            ->map(
                function ($allergy) {
                    if ( ! validAllergyName($this->getAllergyName($allergy))) {
                        return false;
                    }
                    
                    return [
                        'date_range'       => [
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
                        'reaction_type'    => [
                            'name'             => '',
                            'code'             => '',
                            'code_system'      => '',
                            'code_system_name' => '',
                        ],
                        'allergen'         => [
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
    
    private function fillDemographicsSection()
    {
        return [
            'ids'              => [
                'mrn_number' => $this->getMrn(),
            ],
            'name'             => [
                'prefix' => null,
                'given'  => [
                    $this->getFirstName(),
                ],
                'family' => $this->getLastName(),
                'suffix' => null,
            ],
            'dob'              => $this->getDob()->toDateString(),
            'gender'           => $this->data['gender'],
            'mrn_number'       => $this->getMrn(),
            'marital_status'   => '',
            'address'          => [
                'street'  => [
                    $this->getAddressLine1(),
                    $this->getAddressLine2(),
                ],
                'city'    => $this->data['city'],
                'state'   => $this->data['state'],
                'zip'     => $this->getZipCode(),
                'country' => '',
            ],
            'phones'           => [
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
            'email'            => null,
            'language'         => null,
            'race'             => null,
            'ethnicity'        => null,
            'religion'         => null,
            'birthplace'       => [
                'state'   => null,
                'zip'     => null,
                'country' => null,
            ],
            'guardian'         => [
                'name'              => [
                    'given'  => [
                    ],
                    'family' => null,
                ],
                'relationship'      => null,
                'relationship_code' => null,
                'address'           => [
                    'street'  => [
                    ],
                    'city'    => null,
                    'state'   => null,
                    'zip'     => null,
                    'country' => null,
                ],
                'phone'             => [
                    'home' => null,
                ],
            ],
            'patient_contacts' => [
            ],
            'provider'         => [
                'ids'          => [
                ],
                'organization' => null,
                'phones'       => [
                ],
                'address'      => [
                    'street'  => [
                    ],
                    'city'    => null,
                    'state'   => null,
                    'zip'     => null,
                    'country' => null,
                ],
            ],
        ];
    }
    
    private function fillDocumentSection()
    {
        return [
            'custodian'           => [
                'name' => $this->getProviderName(),
            ],
            'date'                => '',
            'title'               => '',
            'author'              => [
                'npi'     => '',
                'name'    => [
                    'prefix' => null,
                    'given'  => [],
                    'family' => null,
                    'suffix' => null,
                ],
                'address' => [
                    'street'  => [
                        0 => '',
                    ],
                    'city'    => '',
                    'state'   => '',
                    'zip'     => '',
                    'country' => '',
                ],
                'phones'  => [
                    0 => [
                        'type'   => '',
                        'number' => '',
                    ],
                ],
            ],
            'documentation_of'    => [
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
                    'phones'      => [
                        0 => [
                            'type'   => '',
                            'number' => '',
                        ],
                    ],
                    'address'     => [
                        'street'  => [
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
                'date'                    => null,
                'ids'                     => [],
                'assigned_person'         => [
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
            'location'            => [
                'name'           => null,
                'address'        => [
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
    
    private function fillMedicationsSection()
    {
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
                        'status'          => $medication->Status,
                        'text'            => null,
                        'product'         => [
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
                        'dose_quantity'   => [
                            'value' => null,
                            'unit'  => null,
                        ],
                        'rate_quantity'   => [
                            'value' => null,
                            'unit'  => null,
                        ],
                        'precondition'    => [
                            'name'        => null,
                            'code'        => null,
                            'code_system' => null,
                        ],
                        'reason'          => [
                            'name'        => null,
                            'code'        => null,
                            'code_system' => null,
                        ],
                        'route'           => [
                            'name'             => null,
                            'code'             => null,
                            'code_system'      => null,
                            'code_system_name' => null,
                        ],
                        'schedule'        => [
                            'type'         => null,
                            'period_value' => null,
                            'period_unit'  => null,
                        ],
                        'vehicle'         => [
                            'name'             => null,
                            'code'             => null,
                            'code_system'      => null,
                            'code_system_name' => null,
                        ],
                        'administration'  => [
                            'name'             => null,
                            'code'             => null,
                            'code_system'      => null,
                            'code_system_name' => null,
                        ],
                        'prescriber'      => [
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
    
    private function fillProblemsSection()
    {
        return collect(collect(json_decode($this->data['problems_string']))->first())
            ->map(
                function ($problem) {
                    if ( ! validProblemName($problem->Name)) {
                        return false;
                    }
                    
                    return [
                        'reference'        => null,
                        'reference_title'  => null,
                        'date_range'       => [
                            'start' => $problem->AddedDate,
                            'end'   => null,
                        ],
                        'name'             => $problem->Name,
                        'status'           => null,
                        'age'              => null,
                        'code'             => $problem->Code,
                        'code_system'      => null,
                        'code_system_name' => $problem->CodeType,
                        'translations'     => [
                            [
                                'name'             => null,
                                'code'             => null,
                                'code_system'      => null,
                                'code_system_name' => null,
                            ],
                        ],
                        'comment'          => null,
                    ];
                }
            )
            ->filter()
            ->values()
            ->toArray();
    }
    
    private function fillVitals()
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
    
    private function getAllergyName($allergy):string
    {
        return $allergy->Name;
    }
    
    public function getProviderName(): string
    {
        return $this->data['referring_provider_name'];
    }
    
    public function getMrn(): string
    {
        return $this->data['mrn'];
    }
    
    public function getFirstName(): string
    {
        return $this->data['first_name'];
    }
    
    public function getDob():Carbon
    {
        return Carbon::parse($this->data['dob']);
    }
    
    public function getLastName(): string
    {
        return $this->data['last_name'];
    }
    
    private function getAddressLine1(): string
    {
        return $this->data['street'];
    }
    
    private function getAddressLine2(): string
    {
        return $this->data['street2'];
    }
    
    private function getZipCode(): string
    {
        return $this->data['zip'];
    }
}