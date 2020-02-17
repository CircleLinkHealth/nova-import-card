<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\UPG\ValueObjects;


class PdfCarePlan
{
    protected $providers;

    protected $chargeableServices;

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Data need some processing after being parsed from pdf
     */
    private function preProcessData()
    {
        $this->data = [
            'first_name'          => isset($this->data['first_name'])
                ? $this->data['first_name']
                : 'N/A',
            'last_name'           => isset($this->data['last_name'])
                ? $this->data['last_name']
                : 'N/A',
            //throw exception if mrn does not exist?
            'patient_id'                 => isset($this->data['mrn'])
                ? $this->data['mrn']
                : 'N/A',
            'date_of_birth'                 => isset($this->data['dob'])
                ? $this->data['dob']
                : 'N/A',
            'gender'                 => isset($this->data['sex'])
                ? $this->data['sex']
                : 'N/A',
            'visit_date'          => isset($this->data['visit_date'])
                ? $this->data['visit_date']
                : 'N/A',
            'address'             => $this->getAddresses(),
            'phones'              => $this->getPhones(),
            //with instructions
            'problems'            => $this->getProblemsWithInstructions(),
            'chargeable_services' => $this->getChargeableServices(),
            'provider'            => $this->getProvider(),
        ];
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
                    $this->data['address'],
                ],
                'city'    => '',
                'state'   => '',
                'zip'     => '',
                'country' => '',
            ],
            'phones' => [
                0 => [
                    'type'   => 'home',
                    'number' => $this->data['phones']['home_phone'] ?? '',
                ],
                1 => [
                    'type'   => 'primary_phone',
                    'number' => $this->data['phones']['primary_phone'] ?: (collect($this->data['phones'])->filter()->first() ?: ''),
                ],
                2 => [
                    'type'   => 'mobile',
                    'number' => $this->data['phones']['cell_phone'] ?? '',
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

    public function toArray()
    {
        $this->preProcessData();

        return [
            'type'         => 'upg0506-pdf-care-plan',
            'document'     => $this->fillDocumentSection(),
            'allergies'    => [],
            'demographics' => $this->fillDemographicsSection(),
            'medications'  => [],
            'payers'       => [
            ],
            'problems'     => $this->fillProblemsSection(),
            'vitals'       => [],
            //add problems with instructions array. Template ['name' => problem name, 'value' => instruction]
            'instructions' => $this->data['problems']
        ];

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
                        'start' => null,
                        'end'   => null,
                    ],
                    'name'             => $problem['name'],
                    'status'           => null,
                    'age'              => null,
                    'code'             => null,
                    'code_system'      => null,
                    'code_system_name' => null,
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

    private function fillDocumentSection()
    {
        return [
            'custodian'           => [
                'name' => $this->data['provider']['full_name'],
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
                            0 => $this->data['provider']['full_name'],
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

    public function toJson()
    {
        return json_encode($this->toArray());
    }

    private function getAddresses()
    {
        //categorize state, zip?
        return isset($this->data['address'])
            ? collect($this->data['address'])->implode(' ')
            : 'N/A';
    }

    private function getPhones()
    {
        $types = [
            [
                'key' => 'primary_phone',
                'search' => 'Primary:'
            ],
            [
                'key'    => 'home_phone',
                'search' => 'Home:',
            ],
            [
                'key'    => 'cell_phone',
                'search' => 'Cell:',
            ],
            [
                'key'    => 'other_phone',
                'search' => 'Other:',
            ],
        ];

        $phones = [];
        //format and categorize phones
        if (isset($this->data['phones'])) {
            foreach ($this->data['phones'] as $string) {

                foreach ($types as $type){
                    if (! isset($phones[$type['key']]) || empty($phones[$type['key']])){
                        $phones[$type['key']] = null;
                        if (str_contains($string, $type['search'])) {
                            $phones[$type['key']] = trim(str_replace($type['search'], ' ', $string));
                        }
                    }

                }


            }

            return $phones;
        }

        return [];
    }

    private function getProblemsWithInstructions()
    {
        $problemsWithInstructions = [];

        if (isset($this->data['problems'])) {
            foreach ($this->data['problems'] as $problemName) {
                if (isset($this->data['instructions'])) {
                    $instructions = collect($this->data['instructions'])->where('problem_name', $problemName)->first();

                    if (isset($instructions['value'])) {
                        $instructionsString = collect($instructions['value'])->implode(' ');
                    }

                    $problemsWithInstructions[] = [
                        'name' => $problemName,
                        'instructions' => isset($instructionsString)
                            ? $instructionsString
                            : 'N/A',
                    ];
                }
            }

            return $problemsWithInstructions;
        }

        return [];
    }

    private function getProvider()
    {
        $provider = [];
        if (isset($this->providers['primary'])) {
            //format: Firstname Lastname SUFFIX
            $primaryProvider = explode(' ', $this->providers['primary']);

            $g0506Service = collect($this->chargeableServices)->where('is_g0506', true)->first();

            if ($g0506Service) {
                //format: Lastname, Firstname
                $g0506Provider = explode(',', $g0506Service['provider']);

                if (
                    strtolower(trim($g0506Provider[1])) === strtolower(trim($primaryProvider[0])) &&
                    strtolower(trim($g0506Provider[0])) === strtolower(trim($primaryProvider[1]))
                ) {
                    $provider['first_name'] = $primaryProvider[0];
                    $provider['last_name']  = $primaryProvider[1];
                    $provider['suffix']     = isset($primaryProvider[2])
                        ? $primaryProvider[2]
                        : null;

                    $fullName = $provider['first_name'] . ' ' . $provider['last_name'];

                    $provider['full_name'] = $provider['suffix']
                        ? $fullName . ' ' . $provider['suffix']
                        : $fullName;
                }
            }
        }

        return $provider;
    }

    private function getChargeableServices()
    {
        $chargeableServices = [];

        if (isset($this->data['chargeable_services'])) {
            $currentChargeableService = [];
            foreach ($this->data['chargeable_services'] as $string) {
                if (strlen($string) === strlen(trim($string))) {
                    if (isset($currentChargeableService['title'])) {
                        $chargeableServices[]     = $currentChargeableService;
                        $currentChargeableService = [];
                    }

                    //todo: fix/improve this check
                    if (str_contains(collect($chargeableServices)->transform(function ($cs) {
                        return $cs['provider'];
                    })->implode(' '), explode(' ', $string))) {
                        $this->providers['primary'] = $string;
                        continue;
                    }
                    $currentChargeableService['title'] = $string;
                } else {
                    $array = explode(':', $string);

                    if (count($array) == 2) {
                        $key   = snake_case(strtolower(trim($array[0])));
                        $value = trim($array[1]);

                        if ($key == 'subject') {
                            $currentChargeableService['is_g0506'] = str_contains(strtolower($value), 'g0506');
                        }

                        $currentChargeableService[$key] = $value;
                    }
                }
            }
            $chargeableServices[] = $currentChargeableService;

            return $this->chargeableServices = collect($chargeableServices)->filter()->toArray();
        }

        return [];
    }
}