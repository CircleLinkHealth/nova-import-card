<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\ValueObjects;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\SharedModels\Entities\Enrollee;

class SurveyOnlyEnrolleeMedicalRecord
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

    /**
     * @param $enrollee
     *
     * @return array
     */
    public function createFromSurveyOnlyUser($enrollee)
    {
        $providerName = $enrollee->provider->display_name;

        $demographics = $this->fillDemographicsSection($enrollee);

        return [
            'type'         => 'surveyOnly-medical-record',
            'document'     => $this->fillDocumentSection($providerName),
            'allergies'    => $this->fillAllergiesSection(),
            'demographics' => $demographics,
            'medications'  => $this->fillMedicationsSection(),
            'payers'       => [
            ],
            'problems'           => $this->fillProblemsSection(),
            'vitals'             => $this->fillVitals(),
            'mrn_number'         => $enrollee->id,
            'preferred_provider' => $providerName,
        ];
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

    private function fillDemographicsSection(Enrollee $enrollee = null)
    {
        $patientId = ! empty($enrollee)
            ? $enrollee->id
            : $this->data['patient_id'];

        $patientFirstName = ! empty($enrollee)
            ? $enrollee->first_name
            : $this->data['first_name'];

        $patientLastName = ! empty($enrollee)
            ? $enrollee->last_name
            : $this->data['last_name'];

        $dob = ! empty($enrollee)
            ? Carbon::parse($enrollee->dob)->toDateString()
            : $this->data['date_of_birth'];

        $address1 = ! empty($enrollee)
            ? $enrollee->address
            : $this->data['address_line_1'];

        $address2 = ! empty($enrollee)
            ? $enrollee->address_2
            : $this->data['address_line_2'];

        $city = ! empty($enrollee)
            ? $enrollee->city
            : $this->data['city'];

        $state = ! empty($enrollee)
            ? $enrollee->state
            : $this->data['state'];

        $gender = ! empty($enrollee)
            ? ''
            : $this->data['gender'];

        $postalCode = ! empty($enrollee)
            ? ''
            : $this->data['postal_code'];

        $homePhone = ! empty($enrollee)
            ? $enrollee->home_phone
            : $this->data['home_phone'];

        $primaryPhone = ! empty($enrollee)
            ? $enrollee->primary_phone
            : $this->data['primary_phone'];

        $cellPhone = ! empty($enrollee)
            ? $enrollee->cell_phone
            : $this->data['cell_phone'];

        return [
            'ids' => [
                'mrn_number' => $patientId,
            ],
            'name' => [
                'prefix' => null,
                'given'  => [
                    $patientFirstName,
                ],
                'family' => $patientLastName,
                'suffix' => null,
            ],
            'dob'            => $dob,
            'gender'         => $gender,
            'mrn_number'     => $patientId,
            'marital_status' => '',
            'address'        => [
                'street' => [
                    $address1,
                    $address2,
                ],
                'city'    => $city,
                'state'   => $state,
                'zip'     => $postalCode,
                'country' => '',
            ],
            'phones' => [
                0 => [
                    'type'   => 'home',
                    'number' => $homePhone ?? '',
                ],
                1 => [
                    'type'   => 'primary_phone',
                    'number' => $primaryPhone,
                ],
                2 => [
                    'type'   => 'mobile',
                    'number' => $cellPhone,
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

    private function fillDocumentSection($providerName = null)
    {
        $prefProvider = ! empty($providerName)
            ? $providerName
            : $this->data['preferred_provider'];

        return [
            'custodian' => [
                'name' => $prefProvider,
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
                            0 => $prefProvider,
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
                        'name'        => $medication['name'],
                        'code'        => '',
                        'code_system' => '',
                        'text'        => $medication['sig'],
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
                if ( ! validProblemName($problem['name'])) {
                    return false;
                }

                return [
                    'reference'       => null,
                    'reference_title' => null,
                    'date_range'      => [
                        'start' => $problem['start_date'],
                        'end'   => null,
                    ],
                    'name'             => $problem['name'],
                    'status'           => null,
                    'age'              => null,
                    'code'             => $problem['code'],
                    'code_system'      => null,
                    'code_system_name' => $problem['code_type'],
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
