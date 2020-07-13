<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecord\Templates;

use App\Models\PracticePull\Allergy;
use App\Models\PracticePull\Demographics;
use App\Models\PracticePull\Medication;
use App\Models\PracticePull\Problem as ProblemModel;
use CircleLinkHealth\Eligibility\MedicalRecord\ValueObjects\Problem as ProblemValueObject;

class PracticePullMedicalRecord extends BaseMedicalRecordTemplate
{
    /**
     * @var array
     */
    protected $data;
    private $demos;
    private $mrn;
    /**
     * @var int
     */
    private $practiceId;

    public function __construct(string $mrn, int $practiceId)
    {
        if (empty($mrn)) {
            throw new \InvalidArgumentException("MRN cannot be empty. practiceId[$mrn]");
        }
        $this->mrn        = $mrn;
        $this->practiceId = $practiceId;
    }

    public function fillAllergiesSection(): array
    {
        return Allergy::where('practice_id', $this->practiceId)->where('mrn', $this->mrn)->get()
            ->map(
                function (Allergy $allergy) {
                    if ( ! validAllergyName($allergy->name)) {
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
                            'name'             => $allergy->name,
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
        $demos = $this->getDemographicsModel();

        return (object) [
            'ids' => [
                'mrn_number' => $demos->mrn,
            ],
            'name' => (object) [
                'prefix' => null,
                'given'  => [
                    $demos->first_name,
                ],
                'family' => $demos->last_name,
                'suffix' => null,
            ],
            'dob'            => $demos->dob->toDateString(),
            'gender'         => $demos->gender,
            'mrn_number'     => $demos->mrn,
            'marital_status' => '',
            'address'        => (object) [
                'street' => [
                    $demos->street,
                    $demos->street2,
                ],
                'city'    => $demos->city,
                'state'   => $demos->state,
                'zip'     => $demos->zip,
                'country' => '',
            ],
            'phones' => [
                0 => (object) [
                    'type'   => 'home',
                    'number' => $demos->home_phone ?? '',
                ],
                1 => (object) [
                    'type'   => 'primary_phone',
                    'number' => $demos->primary_phone ?? '',
                ],
                2 => (object) [
                    'type'   => 'mobile',
                    'number' => $demos->cell_phone ?? '',
                ],
                3 => (object) [
                    'type'   => 'other',
                    'number' => $demos->other_phone ?? '',
                ],
            ],
            'email'      => $demos->email ?? '',
            'language'   => $demos->lang ?? '',
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
                'address'           => (object) [
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
            'provider' => (object) [
                'ids' => [
                ],
                'organization' => null,
                'phones'       => [
                ],
                'address' => (object) [
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
        $demos = $this->getDemographicsModel();

        return (object) [
            'custodian' => [
                'name' => $demos->referring_provider_name,
            ],
            'date'   => '',
            'title'  => '',
            'author' => (object) [
                'npi'  => '',
                'name' => [
                    'prefix' => null,
                    'given'  => [],
                    'family' => null,
                    'suffix' => null,
                ],
                'address' => (object) [
                    'street' => [
                        0 => '',
                    ],
                    'city'    => '',
                    'state'   => '',
                    'zip'     => '',
                    'country' => '',
                ],
                'phones' => [
                    0 => (object) [
                        'type'   => '',
                        'number' => '',
                    ],
                ],
            ],
            'documentation_of' => [
                0 => (object) [
                    'provider_id' => null,
                    'name'        => [
                        'prefix' => null,
                        'given'  => [
                            0 => $demos->referring_provider_name,
                        ],
                        'family' => '',
                        'suffix' => '',
                    ],
                    'phones' => [
                        0 => (object) [
                            'type'   => '',
                            'number' => '',
                        ],
                    ],
                    'address' => (object) [
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
            'legal_authenticator' => (object) [
                'date'            => null,
                'ids'             => [],
                'assigned_person' => [
                    'prefix' => null,
                    'given'  => [],
                    'family' => null,
                    'suffix' => null,
                ],
                'representedOrganization' => (object) [
                    'ids'     => [],
                    'name'    => null,
                    'phones'  => [],
                    'address' => (object) [
                        'street'  => [],
                        'city'    => null,
                        'state'   => null,
                        'zip'     => null,
                        'country' => null,
                    ],
                ],
            ],
            'location' => (object) [
                'name'    => null,
                'address' => (object) [
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

    public function fillEncountersSection(): array
    {
        return [
            (object) [
                'date' => $this->demos->last_encounter,
            ],
        ];
    }

    public function fillMedicationsSection(): array
    {
        return Medication::where('practice_id', $this->practiceId)->where('mrn', $this->mrn)->get()
            ->map(
                function (Medication $medication) {
                    return [
                        'reference'       => null,
                        'reference_title' => null,
                        'reference_sig'   => null,
                        'date_range'      => [
                            'start' => $medication->start ?? null,
                            'end'   => $medication->stop ?? null,
                        ],
                        'status'  => $medication->status,
                        'text'    => null,
                        'product' => [
                            'name'        => $medication->name,
                            'code'        => '',
                            'code_system' => '',
                            'text'        => $medication->sig,
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
        $demos = $this->getDemographicsModel();

        $insurances = [
            'primary_insurance'   => $demos->primary_insurance ?? null,
            'secondary_insurance' => $demos->secondary_insurance ?? null,
            'tertiary_insurance'  => $demos->tertiary_insurance ?? null,
        ];

        return collect($insurances)
            ->filter()
            ->map(
                function ($insurance, $type) {
                    if (empty($insurance)) {
                        return false;
                    }

                    return (object) [
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
        return ProblemModel::where('practice_id', $this->practiceId)->where('mrn', $this->mrn)->get()
            ->map(
                function (ProblemModel $problem) {
                    if ( ! validProblemName($problem->name)) {
                        return false;
                    }

                    return (new ProblemValueObject())
                        ->setName($problem->name)
                        ->setStartDate($problem->start)
                        ->setEndDate($problem->stop)
                        ->setCode($problem->code)
                        ->setCodeSystemName($problem->code_type)
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

    public function getType(): string
    {
        return 'practice-pull-template';
    }

    private function getDemographicsModel()
    {
        if ( ! $this->demos) {
            $this->demos = Demographics::where('practice_id', $this->practiceId)->where('mrn', $this->mrn)->first();
        }

        return $this->demos;
    }
}
