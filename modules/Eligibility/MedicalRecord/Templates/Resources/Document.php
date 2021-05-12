<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecord\Templates\Resources;

use Illuminate\Contracts\Support\Arrayable;

class Document implements Arrayable
{
    public ?string $authorAddress                  = null;
    public ?string $authorName                     = null;
    public ?string $authorNpi                      = null;
    public ?string $custodianName                  = null;
    public ?string $date                           = null;
    public ?string $documentationOfAddress         = null;
    public ?string $documentationOfName            = null;
    public ?string $documentationOfProviderId      = null;
    public ?string $legalAuthenticatorDate         = null;
    public ?string $legalAuthenticatorIds          = null;
    public ?string $legalAuthenticatorName         = null;
    public ?string $locationAddress                = null;
    public ?string $locationEncounterDate          = null;
    public ?string $locationName                   = null;
    public ?string $representedOrganizationAddress = null;
    public ?string $representedOrganizationIds     = null;
    public ?string $representedOrganizationName    = null;
    public ?string $representedOrganizationPhones  = null;
    public ?string $title                          = null;

    public function toArray()
    {
        return [
            'custodian' => [
                'name' => $this->custodianName,
            ],
            'date'   => $this->date,
            'title'  => $this->title,
            'author' => [
                'npi'     => $this->authorNpi,
                'name'    => $this->authorName,
                'address' => $this->authorAddress,
                'phones'  => [
                    0 => [
                        'type'   => '',
                        'number' => '',
                    ],
                ],
            ],
            'documentation_of' => [
                0 => [
                    'provider_id' => $this->documentationOfProviderId,
                    'name'        => $this->documentationOfName,
                    'phones'      => [
                        0 => [
                            'type'   => '',
                            'number' => '',
                        ],
                    ],
                    'address' => $this->documentationOfAddress,
                ],
            ],
            'legal_authenticator' => [
                'date'                    => $this->legalAuthenticatorDate,
                'ids'                     => $this->legalAuthenticatorIds,
                'assigned_person'         => $this->legalAuthenticatorName,
                'representedOrganization' => [
                    'ids'     => $this->representedOrganizationIds,
                    'name'    => $this->representedOrganizationName,
                    'phones'  => $this->representedOrganizationPhones,
                    'address' => $this->representedOrganizationAddress,
                ],
            ],
            'location' => [
                'name'           => $this->locationName,
                'address'        => $this->locationAddress,
                'encounter_date' => $this->locationEncounterDate,
            ],
        ];
    }
}
