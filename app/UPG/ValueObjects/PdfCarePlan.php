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
            'visit' =>isset($this->data['visit_date'])
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

    private function getAddresses(){
        //split to zip etc ?
        return isset($this->data['address']) ? $this->data['address'] : 'N/A';
    }

    private function getPhones(){

        //format and categorize phones
        //add validation

        return isset($this->data['phones']) ? $this->data['phones'] : 'N/A';
    }

    private function getProblemsWithInstructions(){
        return ;
    }
}