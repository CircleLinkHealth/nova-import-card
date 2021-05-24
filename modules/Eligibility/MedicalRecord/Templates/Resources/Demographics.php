<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecord\Templates\Resources;

use Illuminate\Contracts\Support\Arrayable;

class Demographics implements Arrayable
{
    public ?string $birthplaceCountry = null;
    public ?string $birthplaceState   = null;
    public ?string $birthplaceZip     = null;
    public ?string $dob               = null;
    public ?string $email             = null;
    public ?string $ethnicity         = null;
    public ?string $gender            = null;
    public Address $guardianAddress;
    public ?string $guardianHomePhone = null;
    public PersonName $guardianName;
    public ?string $guardianRelationship     = null;
    public ?string $guardianRelationshipCode = null;
    public ?string $language                 = null;
    public ?string $maritalStatus            = null;
    public ?string $mrnNumber                = null;
    public Address $patientAddress;
    public ?string $patientHomePhone   = null;
    public ?string $patientMobilePhone = null;
    public ?string $patientMrn         = null;
    public PersonName $patientName;
    public ?string $patientPrimaryPhone = null;
    public Address $providerAddress;
    public ?string $providerOrganization = null;
    public ?string $race                 = null;
    public ?string $religion             = null;

    public function toArray()
    {
        return [
            'ids' => [
                'mrn_number' => $this->patientMrn,
            ],
            'name'           => $this->patientName,
            'dob'            => $this->dob,
            'gender'         => $this->gender,
            'mrn_number'     => $this->mrnNumber,
            'marital_status' => $this->maritalStatus,
            'address'        => $this->patientAddress,
            'phones'         => [
                0 => [
                    'type'   => 'home',
                    'number' => $this->patientHomePhone,
                ],
                1 => [
                    'type'   => 'primary_phone',
                    'number' => $this->patientPrimaryPhone,
                ],
                2 => [
                    'type'   => 'mobile',
                    'number' => $this->patientMobilePhone,
                ],
            ],
            'email'      => $this->email,
            'language'   => $this->language,
            'race'       => $this->race,
            'ethnicity'  => $this->ethnicity,
            'religion'   => $this->religion,
            'birthplace' => [
                'state'   => $this->birthplaceState,
                'zip'     => $this->birthplaceZip,
                'country' => $this->birthplaceCountry,
            ],
            'guardian' => [
                'name'              => $this->guardianName,
                'relationship'      => $this->guardianRelationship,
                'relationship_code' => $this->guardianRelationshipCode,
                'address'           => $this->guardianAddress,
                'phone'             => [
                    'home' => $this->guardianHomePhone,
                ],
            ],
            'patient_contacts' => [
            ],
            'provider' => [
                'ids'          => [],
                'organization' => $this->providerOrganization,
                'phones'       => [],
                'address'      => $this->providerAddress,
            ],
        ];
    }
}
