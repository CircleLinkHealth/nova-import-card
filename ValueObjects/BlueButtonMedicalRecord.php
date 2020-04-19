<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\ValueObjects;

use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\Customer\Entities\Practice;

class BlueButtonMedicalRecord
{
    /**
     * @var array
     */
    private $data;
    /**
     * @var \CircleLinkHealth\Eligibility\Entities\EligibilityJob
     */
    private $job;
    /**
     * @var Practice
     */
    private $practice;

    public function __construct(EligibilityJob $job, Practice $practice)
    {
        $this->job      = $job;
        $this->data     = $job->data;
        $this->practice = $practice;
    }

    public function toArray()
    {
        return [
            'type'         => 'bluebutton-medical-record',
            'document'     => $this->fillDocumentSection(),
            'allergies'    => $this->fillAllergiesSection(),
            'demographics' => $this->fillDemographicsSection(),
            'medications'  => $this->fillMedicationsSection(),
            'payers'       => [
            ],
            'problems' => $this->fillProblemsSection(),
            'vitals'   => $this->fillVitals(),
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
        return collect($this->data['allergies'] ?? $this->data['Allergies'])
            ->map(function ($allergy) {
                if ( ! validAllergyName($allergy['name'])) {
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
                        'name'             => $allergy['name'],
                        'code'             => '',
                        'code_system'      => '',
                        'code_system_name' => '',
                    ],
                ];
            })
            ->filter()
            ->values()
            ->toArray();
    }

    private function fillDemographicsSection()
    {
        return [
            'ids' => [
                'mrn_number' => $this->data['patient_id'],
            ],
            'name' => [
                'prefix' => null,
                'given'  => [
                    $this->data['first_name'],
                ],
                'family' => $this->data['last_name'],
                'suffix' => null,
            ],
            'dob'            => $this->data['date_of_birth'],
            'gender'         => $this->data['gender'],
            'mrn_number'     => $this->data['patient_id'],
            'marital_status' => '',
            'address'        => [
                'street' => [
                    $this->data['address_line_1'],
                    $this->data['address_line_2'],
                ],
                'city'    => $this->data['city'],
                'state'   => $this->data['state'],
                'zip'     => $this->data['postal_code'],
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

    private function fillDocumentSection()
    {
        return [
            'custodian' => [
                'name' => $this->data['preferred_provider'] ?? null,
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
                            0 => $this->data['preferred_provider'],
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

    private function fillMedicationsSection()
    {
        return collect($this->data['medications'])
            ->map(function ($medication) {
                return [
                    'reference'       => null,
                    'reference_title' => null,
                    'reference_sig'   => null,
                    'date_range'      => [
                        'start' => $medication['startdate'] ?? $medication['start_date'] ?? null,
                        'end'   => $medication['enddate'] ?? $medication['end_date'] ?? null,
                    ],
                    'status'  => '',
                    'text'    => null,
                    'product' => [
                        'name'        => $medication['name'] ?? null,
                        'code'        => '',
                        'code_system' => '',
                        'text'        => $medication['sig'] ?? null,
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
            })
            ->filter()
            ->values()
            ->toArray();
    }

    private function fillProblemsSection()
    {
        return collect($this->data['problems'])
            ->map(function ($problem) {
                if ( ! validProblemName($problem['name'] ?? null)) {
                    return false;
                }

                return [
                    'reference'       => null,
                    'reference_title' => null,
                    'date_range'      => [
                        'start' => $problem['start_date'] ?? null,
                        'end'   => null,
                    ],
                    'name'             => $problem['name'] ?? null,
                    'status'           => null,
                    'age'              => null,
                    'code'             => $problem['code'] ?? null,
                    'code_system'      => null,
                    'code_system_name' => $problem['code_type'] ?? null,
                    'translations'     => [
                        [
                            'name'             => null,
                            'code'             => null,
                            'code_system'      => null,
                            'code_system_name' => null,
                        ],
                    ],
                    'comment' => null,
                ];
            })
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
}
