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

    public function toArray()
    {
        return [
            'first_name'          => isset($this->data['first_name'])
                ? $this->data['first_name']
                : 'N/A',
            'last_name'           => isset($this->data['last_name'])
                ? $this->data['last_name']
                : 'N/A',
            //throw exception if mrn does not exist?
            'mrn'                 => isset($this->data['mrn'])
                ? $this->data['mrn']
                : 'N/A',
            'dob'                 => isset($this->data['dob'])
                ? $this->data['dob']
                : 'N/A',
            'sex'                 => isset($this->data['sex'])
                ? $this->data['sex']
                : 'N/A',
            'visit'               => isset($this->data['visit_date'])
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
            $currentPhone = [];
            foreach ($this->data['phones'] as $string) {
                //each line get key and strip sting, only change index after
                $valueIsSet = false;
                foreach ($types as $type) {
                    if (str_contains($string, $type['search'])) {
                        if (isset($currentPhone['type'])) {
                            $phones[]     = $currentPhone;
                            $currentPhone = [];
                        }
                        $currentPhone['type']    = $type['key'];
                        $currentPhone['value'][] = trim(str_replace($type['search'], ' ', $string));
                        $valueIsSet              = true;
                        break;
                    }
                }
                if ( ! $valueIsSet) {
                    $currentPhone['value'][] = trim(str_replace($type['search'], ' ', $string));
                }

            }
            $phones[] = $currentPhone;

            $phones = collect($phones)->transform(function ($p) {
                return [
                    'type'  => isset($p['type'])
                        ? $p['type']
                        : 'N/A',
                    'value' => collect($p['value'])->implode(' '),
                ];
            })->toArray();

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
                        'problem_name' => $problemName,
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
        if (isset($this->providers['primary'])){
            //format: Firstname Lastname SUFFIX
            $primaryProvider = explode(' ', $this->providers['primary']);

            $g0506Service = collect($this->chargeableServices)->where('is_g0506', true)->first();

            if ($g0506Service){
                //format: Lastname, Firstname
                $g0506Provider = explode(',', $g0506Service['provider']);

                if (
                    strtolower(trim($g0506Provider[1])) === strtolower(trim($primaryProvider[0])) &&
                    strtolower(trim($g0506Provider[0])) === strtolower(trim($primaryProvider[1]))
                ){
                    $provider['first_name'] = $primaryProvider[0];
                    $provider['last_name'] = $primaryProvider[1];
                    $provider['suffix'] = isset($primaryProvider[2]) ? $primaryProvider[2] : null;
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

                        if ($key == 'subject'){
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