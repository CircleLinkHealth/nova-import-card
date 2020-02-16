<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\UPG\ValueObjects;


class PdfCarePlan
{

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function toArray()
    {
        return [
            'first_name' => isset($this->data['first_name'])
                ? $this->data['first_name']
                : 'N/A',
            'last_name'  => isset($this->data['last_name'])
                ? $this->data['last_name']
                : 'N/A',
            //throw exception if mrn does not exist
            'mrn'        => isset($this->data['mrn'])
                ? $this->data['mrn']
                : 'N/A',
            'dob'        => isset($this->data['dob'])
                ? $this->data['dob']
                : 'N/A',
            'sex'        => isset($this->data['sex'])
                ? $this->data['sex']
                : 'N/A',
            'visit'      => isset($this->data['visit_date'])
                ? $this->data['visit_date']
                : 'N/A',
            'address'    => $this->getAddresses(),
            //split to home, cell, other
            'phones'     => $this->getPhones(),
            //with instructions
            'problems'   => $this->getProblemsWithInstructions(),
        ];
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }

    private function getAddresses()
    {
        //split to zip etc ?
        return isset($this->data['address'])
            ? $this->data['address']
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
                            $phones[] = $currentPhone;
                            $currentPhone = [];
                        }
                        //reset object for later iterations
                        //utilize type?
                        $currentPhone['type']  = $type['key'];
                        $currentPhone['value'][] = trim(str_replace($type['search'], ' ', $string));
                        $valueIsSet = true;
                        break;
                    }
                }
                if (! $valueIsSet){
                    $currentPhone['value'][] = trim(str_replace($type['search'], ' ', $string));
                }

            }
            $phones[] = $currentPhone;

            $phones = collect($phones)->transform(function ($p){
                return [
                    'type' => isset($p['type']) ? $p['type'] : 'N/A',
                    'value' => collect($p['value'])->implode(' ')
                ];
            })->toArray();

            return $phones;
        }

        return [];
    }

    private function getProblemsWithInstructions()
    {
        return [];
    }
}