<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 9/21/18
 * Time: 6:49 PM
 */

namespace App\ValueObjects\BlueButtonMedicalRecord;


use App\EligibilityJob;
use App\Practice;

class MedicalRecord
{
    /**
     * @var EligibilityJob
     */
    private $job;

    /**
     * @var array
     */
    private $data;
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
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
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
            'problems'     => $this->fillProblemsSection(),
            'vitals'       => $this->fillVitals(),
        ];
    }

    private function fillDocumentSection()
    {
        return [
            'custodian'           =>
                [
                    'name' => '',
                ],
            'date'                => '',
            'title'               => '',
            'author'              =>
                [
                    'npi'     => '',
                    'name'    =>
                        [
                            'prefix' => null,
                            'given'  => [],
                            'family' => null,
                            'suffix' => null,
                        ],
                    'address' =>
                        [
                            'street'  =>
                                [
                                    0 => '',
                                ],
                            'city'    => '',
                            'state'   => '',
                            'zip'     => '',
                            'country' => '',
                        ],
                    'phones'  =>
                        [
                            0 =>
                                [
                                    'type'   => '',
                                    'number' => '',
                                ],
                        ],
                ],
            'documentation_of'    =>
                [
                    0 =>
                        [
                            'provider_id' => null,
                            'name'        =>
                                [
                                    'prefix' => null,
                                    'given'  =>
                                        [
                                            0 => '',
                                        ],
                                    'family' => '',
                                    'suffix' => '',
                                ],
                            'phones'      =>
                                [
                                    0 =>
                                        [
                                            'type'   => '',
                                            'number' => '',
                                        ],
                                ],
                            'address'     =>
                                [
                                    'street'  =>
                                        [
                                            0 => '',
                                        ],
                                    'city'    => '',
                                    'state'   => '',
                                    'zip'     => '',
                                    'country' => '',
                                ],
                        ],
                ],
            'legal_authenticator' =>
                [
                    'date'                    => null,
                    'ids'                     => [],
                    'assigned_person'         =>
                        [
                            'prefix' => null,
                            'given'  => [],
                            'family' => null,
                            'suffix' => null,
                        ],
                    'representedOrganization' =>
                        [
                            'ids'     => [],
                            'name'    => null,
                            'phones'  => [],
                            'address' =>
                                [
                                    'street'  => [],
                                    'city'    => null,
                                    'state'   => null,
                                    'zip'     => null,
                                    'country' => null,
                                ],
                        ],
                ],
            'location'            =>
                [
                    'name'           => null,
                    'address'        =>
                        [
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

    private function fillAllergiesSection()
    {
        return collect($this->data['allergies'])
            ->map(function ($allergy) {
                if ( ! validAllergyName($allergy['name'])) {
                    return false;
                }

                return [
                    'date_range'       =>
                        [
                            'start' => '',
                            'end'   => null,
                        ],
                    'name'             => null,
                    'code'             => '',
                    'code_system'      => '',
                    'code_system_name' => '',
                    'status'           => null,
                    'severity'         => '',
                    'reaction'         =>
                        [
                            'name'        => '',
                            'code'        => '',
                            'code_system' => '',
                        ],
                    'reaction_type'    =>
                        [
                            'name'             => '',
                            'code'             => '',
                            'code_system'      => '',
                            'code_system_name' => '',
                        ],
                    'allergen'         =>
                        [
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
            'ids'              =>
                [
                    'mrn_number' => $this->data['patient_id'],
                ],
            'name'             =>
                [
                    'prefix' => null,
                    'given'  =>
                        [
                            $this->data['first_name'],
                        ],
                    'family' => $this->data['last_name'],
                    'suffix' => null,
                ],
            'dob'              => $this->data['date_of_birth'],
            'gender'           => '',
            'mrn_number'       => $this->data['patient_id'],
            'marital_status'   => '',
            'address'          =>
                [
                    'street'  =>
                        [
                            $this->data['address_line_1'],
                            $this->data['address_line_2'],
                        ],
                    'city'    => $this->data['city'],
                    'state'   => $this->data['state'],
                    'zip'     => $this->data['postal_code'],
                    'country' => '',
                ],
            'phones'           =>
                [
                    0 =>
                        [
                            'type'   => 'home',
                            'number' => $this->data['home_phone'] ?? '',
                        ],
                    1 =>
                        [
                            'type'   => 'primary_phone',
                            'number' => $this->data['primary_phone'],
                        ],
                    2 =>
                        [
                            'type'   => 'mobile',
                            'number' => $this->data['cell_phone'],
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

    private function fillMedicationsSection()
    {
        return collect($this->data['medications'])
            ->map(function ($medication) {
                return [
                    'reference'       => null,
                    'reference_title' => null,
                    'reference_sig'   => null,
                    'date_range'      =>
                        [
                            'start' => $medication['startdate'],
                            'end'   => null,
                        ],
                    'status'          => '',
                    'text'            => null,
                    'product'         =>
                        [
                            'name'        => $medication['name'],
                            'code'        => '',
                            'code_system' => '',
                            'text'        => $medication['sig'],
                            'translation' =>
                                [
                                    'name'             => null,
                                    'code'             => null,
                                    'code_system'      => null,
                                    'code_system_name' => null,
                                ],
                        ],
                    'dose_quantity'   =>
                        [
                            'value' => null,
                            'unit'  => null,
                        ],
                    'rate_quantity'   =>
                        [
                            'value' => null,
                            'unit'  => null,
                        ],
                    'precondition'    =>
                        [
                            'name'        => null,
                            'code'        => null,
                            'code_system' => null,
                        ],
                    'reason'          =>
                        [
                            'name'        => null,
                            'code'        => null,
                            'code_system' => null,
                        ],
                    'route'           =>
                        [
                            'name'             => null,
                            'code'             => null,
                            'code_system'      => null,
                            'code_system_name' => null,
                        ],
                    'schedule'        =>
                        [
                            'type'         => null,
                            'period_value' => null,
                            'period_unit'  => null,
                        ],
                    'vehicle'         =>
                        [
                            'name'             => null,
                            'code'             => null,
                            'code_system'      => null,
                            'code_system_name' => null,
                        ],
                    'administration'  =>
                        [
                            'name'             => null,
                            'code'             => null,
                            'code_system'      => null,
                            'code_system_name' => null,
                        ],
                    'prescriber'      =>
                        [
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
                    'reference'        => null,
                    'reference_title'  => null,
                    'date_range'       =>
                        [
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
                    'comment'          => null,
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
                'results' =>
                    [
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