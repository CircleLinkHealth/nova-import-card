<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Tests;

use CircleLinkHealth\Eligibility\Console\ReimportPatientMedicalRecord;
use CircleLinkHealth\Eligibility\MedicalRecord\Templates\CcdaMedicalRecord;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\ValidationException;
use Tests\CustomerTestCase;

class DiabetesEndocrineMedicalRecordTest extends CustomerTestCase
{
    public function expectedResult()
    {
        return [
            'type'         => 'csv-with-json',
            'document'     =>
                (object)
                [
                    'custodian'           =>
                        [
                            'name' => 'Demo, Dr MD',
                        ],
                    'date'                => '',
                    'title'               => '',
                    'author'              =>
                        [
                            'npi'     => '',
                            'name'    =>
                                [
                                    'prefix' => null,
                                    'given'  =>
                                        [
                                        ],
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
                                                    0 => 'Demo, Dr MD',
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
                            'ids'                     =>
                                [
                                ],
                            'assigned_person'         =>
                                [
                                    'prefix' => null,
                                    'given'  =>
                                        [
                                        ],
                                    'family' => null,
                                    'suffix' => null,
                                ],
                            'representedOrganization' =>
                                [
                                    'ids'     =>
                                        [
                                        ],
                                    'name'    => null,
                                    'phones'  =>
                                        [
                                        ],
                                    'address' =>
                                        [
                                            'street'  =>
                                                [
                                                ],
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
                                    'street'  =>
                                        [
                                        ],
                                    'city'    => null,
                                    'state'   => null,
                                    'zip'     => null,
                                    'country' => null,
                                ],
                            'encounter_date' => null,
                        ],
                ],
            'allergies'    => [
                '0' =>
                    [
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
                                'name'             => 'Percocet',
                                'code'             => '',
                                'code_system'      => '',
                                'code_system_name' => '',
                            ],
                    ],
            ],
            'demographics' =>
                (object) [
                    'ids'              =>
                        [
                            'mrn_number' => '1234test',
                        ],
                    'name'             =>
                        [
                            'prefix' => null,
                            'given'  =>
                                [
                                    0 => 'John Test',
                                ],
                            'family' => 'Doe Test',
                            'suffix' => null,
                        ],
                    'dob'              => '1970-04-03',
                    'gender'           => 'Female',
                    'mrn_number'       => '1234test',
                    'marital_status'   => '',
                    'address'          =>
                        [
                            'street'  =>
                                [
                                    0 => '1234 Test Avenue',
                                    1 => '',
                                ],
                            'city'    => 'Stamford',
                            'state'   => 'CT',
                            'zip'     => '12345',
                            'country' => '',
                        ],
                    'phones'           =>
                        [
                            0 =>
                                [
                                    'type'   => 'home',
                                    'number' => '111-111-3333',
                                ],
                            1 =>
                                [
                                    'type'   => 'primary_phone',
                                    'number' => 'Cell',
                                ],
                            2 =>
                                [
                                    'type'   => 'mobile',
                                    'number' => '111-111-2222',
                                ],
                        ],
                    'email'            => null,
                    'language'         => null,
                    'race'             => null,
                    'ethnicity'        => null,
                    'religion'         => null,
                    'birthplace'       =>
                        [
                            'state'   => null,
                            'zip'     => null,
                            'country' => null,
                        ],
                    'guardian'         =>
                        [
                            'name'              =>
                                [
                                    'given'  =>
                                        [
                                        ],
                                    'family' => null,
                                ],
                            'relationship'      => null,
                            'relationship_code' => null,
                            'address'           =>
                                [
                                    'street'  =>
                                        [
                                        ],
                                    'city'    => null,
                                    'state'   => null,
                                    'zip'     => null,
                                    'country' => null,
                                ],
                            'phone'             =>
                                [
                                    'home' => null,
                                ],
                        ],
                    'patient_contacts' =>
                        [
                        ],
                    'provider'         =>
                        [
                            'ids'          =>
                                [
                                ],
                            'organization' => null,
                            'phones'       =>
                                [
                                ],
                            'address'      =>
                                [
                                    'street'  =>
                                        [
                                        ],
                                    'city'    => null,
                                    'state'   => null,
                                    'zip'     => null,
                                    'country' => null,
                                ],
                        ],
                ],
            
            'medications' => [
                '0'  =>
                    [
                        'reference'       => null,
                        'reference_title' => null,
                        'reference_sig'   => null,
                        'date_range'      =>
                            [
                                'start' => '02/19/2019',
                                'end'   => '',
                            ],
                        'status'          => 'Taking',
                        'text'            => null,
                        'product'         =>
                            [
                                'name'        => 'Triamcinolone Acetonide 0.1 % Cream',
                                'code'        => '',
                                'code_system' => '',
                                'text'        => '1 application to affected area as needed Externally Twice a day 14 days',
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
                    ],
                '1'  =>
                    [
                        'reference'       => null,
                        'reference_title' => null,
                        'reference_sig'   => null,
                        'date_range'      =>
                            [
                                'start' => '',
                                'end'   => '',
                            ],
                        'status'          => 'Taking',
                        'text'            => null,
                        'product'         =>
                            [
                                'name'        => 'Duloxetine HCl 60 MG Capsule Delayed Release Particles',
                                'code'        => '',
                                'code_system' => '',
                                'text'        => '1 capsule Orally Once a day 30',
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
                    ],
                '2'  =>
                    [
                        'reference'       => null,
                        'reference_title' => null,
                        'reference_sig'   => null,
                        'date_range'      =>
                            [
                                'start' => '',
                                'end'   => '',
                            ],
                        'status'          => 'Taking',
                        'text'            => null,
                        'product'         =>
                            [
                                'name'        => 'Hydrochlorothiazide 12.5 MG Capsule',
                                'code'        => '',
                                'code_system' => '',
                                'text'        => '1 capsule in the morning Orally Once a day 90',
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
                    ],
                '3'  =>
                    [
                        'reference'       => null,
                        'reference_title' => null,
                        'reference_sig'   => null,
                        'date_range'      =>
                            [
                                'start' => '',
                                'end'   => '',
                            ],
                        'status'          => 'Taking',
                        'text'            => null,
                        'product'         =>
                            [
                                'name'        => 'Naproxen 250 MG Tablet',
                                'code'        => '',
                                'code_system' => '',
                                'text'        => '1 - 2 tablets Orally Twice a day 90',
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
                    ],
                '4'  =>
                    [
                        'reference'       => null,
                        'reference_title' => null,
                        'reference_sig'   => null,
                        'date_range'      =>
                            [
                                'start' => '',
                                'end'   => '',
                            ],
                        'status'          => 'Not Taking',
                        'text'            => null,
                        'product'         =>
                            [
                                'name'        => 'Trazodone HCl 100 MG Tablet',
                                'code'        => '',
                                'code_system' => '',
                                'text'        => '1 tablet at bedtime Orally Once a day 30',
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
                    ],
                '5'  =>
                    [
                        'reference'       => null,
                        'reference_title' => null,
                        'reference_sig'   => null,
                        'date_range'      =>
                            [
                                'start' => '',
                                'end'   => '',
                            ],
                        'status'          => 'Not Taking',
                        'text'            => null,
                        'product'         =>
                            [
                                'name'        => 'Clonazepam 0.5 MG Tablet',
                                'code'        => '',
                                'code_system' => '',
                                'text'        => '1 tablet Orally Twice a day ',
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
                    ],
                '6'  =>
                    [
                        'reference'       => null,
                        'reference_title' => null,
                        'reference_sig'   => null,
                        'date_range'      =>
                            [
                                'start' => '',
                                'end'   => '',
                            ],
                        'status'          => 'Not Taking',
                        'text'            => null,
                        'product'         =>
                            [
                                'name'        => 'Mirtazapine 30 MG Tablet',
                                'code'        => '',
                                'code_system' => '',
                                'text'        => '1 tablet at bedtime Orally Once a day 30',
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
                    ],
                '7'  =>
                    [
                        'reference'       => null,
                        'reference_title' => null,
                        'reference_sig'   => null,
                        'date_range'      =>
                            [
                                'start' => '',
                                'end'   => '',
                            ],
                        'status'          => 'Taking',
                        'text'            => null,
                        'product'         =>
                            [
                                'name'        => 'Cyclobenzaprine HCl 10 MG Tablet',
                                'code'        => '',
                                'code_system' => '',
                                'text'        => '1 tablet as needed Orally BID 30',
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
                    ],
                '8'  =>
                    [
                        'reference'       => null,
                        'reference_title' => null,
                        'reference_sig'   => null,
                        'date_range'      =>
                            [
                                'start' => '',
                                'end'   => '',
                            ],
                        'status'          => 'Taking',
                        'text'            => null,
                        'product'         =>
                            [
                                'name'        => 'Gabapentin 300 Capsule',
                                'code'        => '',
                                'code_system' => '',
                                'text'        => '1 capsule Orally TID 30',
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
                    ],
                '9'  =>
                    [
                        'reference'       => null,
                        'reference_title' => null,
                        'reference_sig'   => null,
                        'date_range'      =>
                            [
                                'start' => '',
                                'end'   => '',
                            ],
                        'status'          => 'Taking',
                        'text'            => null,
                        'product'         =>
                            [
                                'name'        => 'Lamotrigine 100 MG Tablet',
                                'code'        => '',
                                'code_system' => '',
                                'text'        => '1 tablet Orally twice a day 30 days',
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
                    ],
                '10' =>
                    [
                        'reference'       => null,
                        'reference_title' => null,
                        'reference_sig'   => null,
                        'date_range'      =>
                            [
                                'start' => '01/29/2018',
                                'end'   => '',
                            ],
                        'status'          => 'Not Taking',
                        'text'            => null,
                        'product'         =>
                            [
                                'name'        => 'Permethrin 1 % Liquid',
                                'code'        => '',
                                'code_system' => '',
                                'text'        => '1 application to affected area Externally Once a day, make repeat in 10 days if symptoms persist 1 dose',
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
                    ],
                '11' =>
                    [
                        'reference'       => null,
                        'reference_title' => null,
                        'reference_sig'   => null,
                        'date_range'      =>
                            [
                                'start' => '02/22/2018',
                                'end'   => '',
                            ],
                        'status'          => 'Not Taking',
                        'text'            => null,
                        'product'         =>
                            [
                                'name'        => 'Fluticasone Propionate 50 MCG/ACT Suspension',
                                'code'        => '',
                                'code_system' => '',
                                'text'        => '1 spray in each nostril Nasally Once a day 30 day(s)',
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
                    ],
                '12' =>
                    [
                        'reference'       => null,
                        'reference_title' => null,
                        'reference_sig'   => null,
                        'date_range'      =>
                            [
                                'start' => '02/22/2018',
                                'end'   => '',
                            ],
                        'status'          => 'Not Taking',
                        'text'            => null,
                        'product'         =>
                            [
                                'name'        => 'Loratadine 10 MG Tablet',
                                'code'        => '',
                                'code_system' => '',
                                'text'        => '1 tablet Orally Once a day 30 day(s)',
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
                    ],
                '13' =>
                    [
                        'reference'       => null,
                        'reference_title' => null,
                        'reference_sig'   => null,
                        'date_range'      =>
                            [
                                'start' => '04/12/2018',
                                'end'   => '',
                            ],
                        'status'          => 'Taking',
                        'text'            => null,
                        'product'         =>
                            [
                                'name'        => 'Tramadol HCl 50 MG Tablet',
                                'code'        => '',
                                'code_system' => '',
                                'text'        => '1-2 tablets as needed Orally TID 30 days',
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
                    ],
                '14' =>
                    [
                        'reference'       => null,
                        'reference_title' => null,
                        'reference_sig'   => null,
                        'date_range'      =>
                            [
                                'start' => '04/18/2018',
                                'end'   => '',
                            ],
                        'status'          => 'Not Taking',
                        'text'            => null,
                        'product'         =>
                            [
                                'name'        => 'Pregabalin 75 MG Capsule',
                                'code'        => '',
                                'code_system' => '',
                                'text'        => '1 capsule twice daily Orally BID 30 days',
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
                    ],
            ],
            'payers'      => [
            ],
            'problems'    => [
                '0' =>
                    (object) [
                        'reference'        => null,
                        'reference_title'  => null,
                        'date_range'       =>
                            (object) [
                                'start' => '04/05/2017',
                                'end'   => '',
                            ],
                        'name'             => 'Other chronic pain',
                        'status'           => null,
                        'age'              => null,
                        'code'             => 'G89.29',
                        'code_system'      => null,
                        'code_system_name' => 'ICD10',
                        'translations'     =>
                            [
                                0 => null,
                            ],
                        'comment'          => null,
                    ],
                '1' =>
                    (object) [
                        'reference'        => null,
                        'reference_title'  => null,
                        'date_range'       =>
                            (object) [
                                'start' => '04/05/2017',
                                'end'   => '',
                            
                            ],
                        'name'             => 'Lumbago with sciatica, right side',
                        'status'           => null,
                        'age'              => null,
                        'code'             => 'M54.41',
                        'code_system'      => null,
                        'code_system_name' => 'ICD10',
                        'translations'     =>
                            [
                                0 => null,
                            ],
                        'comment'          => null,
                    ],
                '2' =>
                    (object) [
                        'reference'        => null,
                        'reference_title'  => null,
                        'date_range'       =>
                            (object) [
                                'start' => '02/19/2019',
                                'end'   => '',
                            
                            ],
                        'name'             => 'Lumbago with sciatica, left side',
                        'status'           => null,
                        'age'              => null,
                        'code'             => 'M54.42',
                        'code_system'      => null,
                        'code_system_name' => 'ICD10',
                        'translations'     =>
                            [
                                0 => null,
                            ],
                        'comment'          => null,
                    ],
                '3' =>
                    (object) [
                        'reference'        => null,
                        'reference_title'  => null,
                        'date_range'       =>
                            (object) [
                                'start' => '04/05/2017',
                                'end'   => '',
                            
                            ],
                        'name'             => 'Essential hypertension',
                        'status'           => null,
                        'age'              => null,
                        'code'             => 'I10',
                        'code_system'      => null,
                        'code_system_name' => 'ICD10',
                        'translations'     =>
                            [
                                0 => null,
                            ],
                        'comment'          => null,
                    ],
                '4' =>
                    (object) [
                        'reference'        => null,
                        'reference_title'  => null,
                        'date_range'       =>
                            (object) [
                                'start' => '10/16/2018',
                                'end'   => '',
                            
                            ],
                        'name'             => 'Neuropathy',
                        'status'           => null,
                        'age'              => null,
                        'code'             => 'G62.9',
                        'code_system'      => null,
                        'code_system_name' => 'ICD10',
                        'translations'     =>
                            [
                                0 => null,
                            ],
                        'comment'          => null,
                    ],
                '5' =>
                    (object) [
                        'reference'        => null,
                        'reference_title'  => null,
                        'date_range'       =>
                            (object) [
                                'start' => '04/05/2017',
                                'end'   => '',
                            
                            ],
                        'name'             => 'Anxiety',
                        'status'           => null,
                        'age'              => null,
                        'code'             => 'F41.9',
                        'code_system'      => null,
                        'code_system_name' => 'ICD10',
                        'translations'     =>
                            [
                                0 => null,
                            ],
                        'comment'          => null,
                    ],
                '6' =>
                    (object) [
                        'reference'        => null,
                        'reference_title'  => null,
                        'date_range'       =>
                            (object) [
                                'start' => '09/17/2018',
                                'end'   => '',
                            ],
                        'name'             => 'Moderate episode of recurrent major depressive disorder',
                        'status'           => null,
                        'age'              => null,
                        'code'             => 'F33.1',
                        'code_system'      => null,
                        'code_system_name' => 'ICD10',
                        'translations'     =>
                            [
                                0 => null,
                            ],
                        'comment'          => null,
                    ],
                '7' =>
                    (object) [
                        'reference'        => null,
                        'reference_title'  => null,
                        'date_range'       =>
                            (object) [
                                'start' => '10/16/2018',
                                'end'   => '',
                            ],
                        'name'             => 'Reactive depression',
                        'status'           => null,
                        'age'              => null,
                        'code'             => 'F32.9',
                        'code_system'      => null,
                        'code_system_name' => 'ICD10',
                        'translations'     =>
                            [
                                0 => null,
                            ],
                        'comment'          => null,
                    ],
                '8' =>
                    (object) [
                        'reference'        => null,
                        'reference_title'  => null,
                        'date_range'       =>
                            (object) [
                                'start' => '04/05/2017',
                                'end'   => '',
                            ],
                        'name'             => 'Short-term memory loss',
                        'status'           => null,
                        'age'              => null,
                        'code'             => 'R41.3',
                        'code_system'      => null,
                        'code_system_name' => 'ICD10',
                        'translations'     =>
                            [
                                0 => null,
                            ],
                        'comment'          => null,
                    ],
                '9' =>
                    (object) [
                        'reference'        => null,
                        'reference_title'  => null,
                        'date_range'       =>
                            (object) [
                                'start' => '02/14/2019',
                                'end'   => '',
                            ],
                        'name'             => 'Poor short term memory',
                        'status'           => null,
                        'age'              => null,
                        'code'             => 'R41.3',
                        'code_system'      => null,
                        'code_system_name' => 'ICD10',
                        'translations'     =>
                            [
                                0 => null,
                            ],
                        'comment'          => null,
                    ],
            ],
            'vitals'      => [
                '0' =>
                    [
                        'date'    => null,
                        'results' =>
                            [
                                0 =>
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
            ],
        ];
    }
    
    public function getFakeMedicalRecord()
    {
        return json_decode(
            '{"type":"bluebutton-medical-record","document":{"custodian":{"name":"Jimi Spage, MD"},"date":"","title":"","author":{"npi":"","name":{"prefix":null,"given":[],"family":null,"suffix":null},"address":{"street":[""],"city":"","state":"","zip":"","country":""},"phones":[{"type":"","number":""}]},"documentation_of":[{"provider_id":null,"name":{"prefix":null,"given":["Jimi Spage, MD"],"family":"","suffix":""},"phones":[{"type":"","number":""}],"address":{"street":[""],"city":"","state":"","zip":"","country":""}}],"legal_authenticator":{"date":null,"ids":[],"assigned_person":{"prefix":null,"given":[],"family":null,"suffix":null},"representedOrganization":{"ids":[],"name":null,"phones":[],"address":{"street":[],"city":null,"state":null,"zip":null,"country":null}}},"location":{"name":null,"address":{"street":[],"city":null,"state":null,"zip":null,"country":null},"encounter_date":null}},"allergies":[{"date_range":{"start":"","end":null},"name":null,"code":"","code_system":"","code_system_name":"","status":null,"severity":"","reaction":{"name":"","code":"","code_system":""},"reaction_type":{"name":"","code":"","code_system":"","code_system_name":""},"allergen":{"name":"Cephalexin Powder","code":"","code_system":"","code_system_name":""}},{"date_range":{"start":"","end":null},"name":null,"code":"","code_system":"","code_system_name":"","status":null,"severity":"","reaction":{"name":"","code":"","code_system":""},"reaction_type":{"name":"","code":"","code_system":"","code_system_name":""},"allergen":{"name":"Penicillins","code":"","code_system":"","code_system_name":""}},{"date_range":{"start":"","end":null},"name":null,"code":"","code_system":"","code_system_name":"","status":null,"severity":"","reaction":{"name":"","code":"","code_system":""},"reaction_type":{"name":"","code":"","code_system":"","code_system_name":""},"allergen":{"name":"Sulfa Antibiotics","code":"","code_system":"","code_system_name":""}}],"demographics":{"ids":{"mrn_number":"1111111111"},"name":{"prefix":null,"given":["Foo"],"family":"Bar","suffix":null},"dob":"01\/01\/1970","gender":"F","mrn_number":"1111111111","marital_status":"","address":{"street":["1234 Summer Street",""],"city":"NYC","state":"NY","zip":"12345","country":""},"phones":[{"type":"home","number":""},{"type":"primary_phone","number":"1231231234"},{"type":"mobile","number":""}],"email":null,"language":null,"race":null,"ethnicity":null,"religion":null,"birthplace":{"state":null,"zip":null,"country":null},"guardian":{"name":{"given":[],"family":null},"relationship":null,"relationship_code":null,"address":{"street":[],"city":null,"state":null,"zip":null,"country":null},"phone":{"home":null}},"patient_contacts":[],"provider":{"ids":[],"organization":null,"phones":[],"address":{"street":[],"city":null,"state":null,"zip":null,"country":null}}},"medications":[{"reference":null,"reference_title":null,"reference_sig":null,"date_range":{"start":null,"end":null},"status":"","text":null,"product":{"name":"hydrALAZINE HCl","code":"","code_system":"","text":null,"translation":{"name":null,"code":null,"code_system":null,"code_system_name":null}},"dose_quantity":{"value":null,"unit":null},"rate_quantity":{"value":null,"unit":null},"precondition":{"name":null,"code":null,"code_system":null},"reason":{"name":null,"code":null,"code_system":null},"route":{"name":null,"code":null,"code_system":null,"code_system_name":null},"schedule":{"type":null,"period_value":null,"period_unit":null},"vehicle":{"name":null,"code":null,"code_system":null,"code_system_name":null},"administration":{"name":null,"code":null,"code_system":null,"code_system_name":null},"prescriber":{"organization":null,"person":null}},{"reference":null,"reference_title":null,"reference_sig":null,"date_range":{"start":null,"end":null},"status":"","text":null,"product":{"name":"Fish Oil","code":"","code_system":"","text":null,"translation":{"name":null,"code":null,"code_system":null,"code_system_name":null}},"dose_quantity":{"value":null,"unit":null},"rate_quantity":{"value":null,"unit":null},"precondition":{"name":null,"code":null,"code_system":null},"reason":{"name":null,"code":null,"code_system":null},"route":{"name":null,"code":null,"code_system":null,"code_system_name":null},"schedule":{"type":null,"period_value":null,"period_unit":null},"vehicle":{"name":null,"code":null,"code_system":null,"code_system_name":null},"administration":{"name":null,"code":null,"code_system":null,"code_system_name":null},"prescriber":{"organization":null,"person":null}},{"reference":null,"reference_title":null,"reference_sig":null,"date_range":{"start":null,"end":null},"status":"","text":null,"product":{"name":"Nystatin","code":"","code_system":"","text":null,"translation":{"name":null,"code":null,"code_system":null,"code_system_name":null}},"dose_quantity":{"value":null,"unit":null},"rate_quantity":{"value":null,"unit":null},"precondition":{"name":null,"code":null,"code_system":null},"reason":{"name":null,"code":null,"code_system":null},"route":{"name":null,"code":null,"code_system":null,"code_system_name":null},"schedule":{"type":null,"period_value":null,"period_unit":null},"vehicle":{"name":null,"code":null,"code_system":null,"code_system_name":null},"administration":{"name":null,"code":null,"code_system":null,"code_system_name":null},"prescriber":{"organization":null,"person":null}},{"reference":null,"reference_title":null,"reference_sig":null,"date_range":{"start":null,"end":null},"status":"","text":null,"product":{"name":"GNP Vitamin D Maximum Strength","code":"","code_system":"","text":null,"translation":{"name":null,"code":null,"code_system":null,"code_system_name":null}},"dose_quantity":{"value":null,"unit":null},"rate_quantity":{"value":null,"unit":null},"precondition":{"name":null,"code":null,"code_system":null},"reason":{"name":null,"code":null,"code_system":null},"route":{"name":null,"code":null,"code_system":null,"code_system_name":null},"schedule":{"type":null,"period_value":null,"period_unit":null},"vehicle":{"name":null,"code":null,"code_system":null,"code_system_name":null},"administration":{"name":null,"code":null,"code_system":null,"code_system_name":null},"prescriber":{"organization":null,"person":null}},{"reference":null,"reference_title":null,"reference_sig":null,"date_range":{"start":null,"end":null},"status":"","text":null,"product":{"name":"4X Probiotic","code":"","code_system":"","text":null,"translation":{"name":null,"code":null,"code_system":null,"code_system_name":null}},"dose_quantity":{"value":null,"unit":null},"rate_quantity":{"value":null,"unit":null},"precondition":{"name":null,"code":null,"code_system":null},"reason":{"name":null,"code":null,"code_system":null},"route":{"name":null,"code":null,"code_system":null,"code_system_name":null},"schedule":{"type":null,"period_value":null,"period_unit":null},"vehicle":{"name":null,"code":null,"code_system":null,"code_system_name":null},"administration":{"name":null,"code":null,"code_system":null,"code_system_name":null},"prescriber":{"organization":null,"person":null}},{"reference":null,"reference_title":null,"reference_sig":null,"date_range":{"start":null,"end":null},"status":"","text":null,"product":{"name":"Vitamin C","code":"","code_system":"","text":null,"translation":{"name":null,"code":null,"code_system":null,"code_system_name":null}},"dose_quantity":{"value":null,"unit":null},"rate_quantity":{"value":null,"unit":null},"precondition":{"name":null,"code":null,"code_system":null},"reason":{"name":null,"code":null,"code_system":null},"route":{"name":null,"code":null,"code_system":null,"code_system_name":null},"schedule":{"type":null,"period_value":null,"period_unit":null},"vehicle":{"name":null,"code":null,"code_system":null,"code_system_name":null},"administration":{"name":null,"code":null,"code_system":null,"code_system_name":null},"prescriber":{"organization":null,"person":null}},{"reference":null,"reference_title":null,"reference_sig":null,"date_range":{"start":null,"end":null},"status":"","text":null,"product":{"name":"traMADol HCl","code":"","code_system":"","text":null,"translation":{"name":null,"code":null,"code_system":null,"code_system_name":null}},"dose_quantity":{"value":null,"unit":null},"rate_quantity":{"value":null,"unit":null},"precondition":{"name":null,"code":null,"code_system":null},"reason":{"name":null,"code":null,"code_system":null},"route":{"name":null,"code":null,"code_system":null,"code_system_name":null},"schedule":{"type":null,"period_value":null,"period_unit":null},"vehicle":{"name":null,"code":null,"code_system":null,"code_system_name":null},"administration":{"name":null,"code":null,"code_system":null,"code_system_name":null},"prescriber":{"organization":null,"person":null}},{"reference":null,"reference_title":null,"reference_sig":null,"date_range":{"start":null,"end":null},"status":"","text":null,"product":{"name":"Chromium","code":"","code_system":"","text":null,"translation":{"name":null,"code":null,"code_system":null,"code_system_name":null}},"dose_quantity":{"value":null,"unit":null},"rate_quantity":{"value":null,"unit":null},"precondition":{"name":null,"code":null,"code_system":null},"reason":{"name":null,"code":null,"code_system":null},"route":{"name":null,"code":null,"code_system":null,"code_system_name":null},"schedule":{"type":null,"period_value":null,"period_unit":null},"vehicle":{"name":null,"code":null,"code_system":null,"code_system_name":null},"administration":{"name":null,"code":null,"code_system":null,"code_system_name":null},"prescriber":{"organization":null,"person":null}},{"reference":null,"reference_title":null,"reference_sig":null,"date_range":{"start":null,"end":null},"status":"","text":null,"product":{"name":"Meloxicam","code":"","code_system":"","text":null,"translation":{"name":null,"code":null,"code_system":null,"code_system_name":null}},"dose_quantity":{"value":null,"unit":null},"rate_quantity":{"value":null,"unit":null},"precondition":{"name":null,"code":null,"code_system":null},"reason":{"name":null,"code":null,"code_system":null},"route":{"name":null,"code":null,"code_system":null,"code_system_name":null},"schedule":{"type":null,"period_value":null,"period_unit":null},"vehicle":{"name":null,"code":null,"code_system":null,"code_system_name":null},"administration":{"name":null,"code":null,"code_system":null,"code_system_name":null},"prescriber":{"organization":null,"person":null}},{"reference":null,"reference_title":null,"reference_sig":null,"date_range":{"start":null,"end":null},"status":"","text":null,"product":{"name":"Evista","code":"","code_system":"","text":null,"translation":{"name":null,"code":null,"code_system":null,"code_system_name":null}},"dose_quantity":{"value":null,"unit":null},"rate_quantity":{"value":null,"unit":null},"precondition":{"name":null,"code":null,"code_system":null},"reason":{"name":null,"code":null,"code_system":null},"route":{"name":null,"code":null,"code_system":null,"code_system_name":null},"schedule":{"type":null,"period_value":null,"period_unit":null},"vehicle":{"name":null,"code":null,"code_system":null,"code_system_name":null},"administration":{"name":null,"code":null,"code_system":null,"code_system_name":null},"prescriber":{"organization":null,"person":null}},{"reference":null,"reference_title":null,"reference_sig":null,"date_range":{"start":null,"end":null},"status":"","text":null,"product":{"name":"Lipitor","code":"","code_system":"","text":null,"translation":{"name":null,"code":null,"code_system":null,"code_system_name":null}},"dose_quantity":{"value":null,"unit":null},"rate_quantity":{"value":null,"unit":null},"precondition":{"name":null,"code":null,"code_system":null},"reason":{"name":null,"code":null,"code_system":null},"route":{"name":null,"code":null,"code_system":null,"code_system_name":null},"schedule":{"type":null,"period_value":null,"period_unit":null},"vehicle":{"name":null,"code":null,"code_system":null,"code_system_name":null},"administration":{"name":null,"code":null,"code_system":null,"code_system_name":null},"prescriber":{"organization":null,"person":null}},{"reference":null,"reference_title":null,"reference_sig":null,"date_range":{"start":null,"end":null},"status":"","text":null,"product":{"name":"Lipitor","code":"","code_system":"","text":null,"translation":{"name":null,"code":null,"code_system":null,"code_system_name":null}},"dose_quantity":{"value":null,"unit":null},"rate_quantity":{"value":null,"unit":null},"precondition":{"name":null,"code":null,"code_system":null},"reason":{"name":null,"code":null,"code_system":null},"route":{"name":null,"code":null,"code_system":null,"code_system_name":null},"schedule":{"type":null,"period_value":null,"period_unit":null},"vehicle":{"name":null,"code":null,"code_system":null,"code_system_name":null},"administration":{"name":null,"code":null,"code_system":null,"code_system_name":null},"prescriber":{"organization":null,"person":null}},{"reference":null,"reference_title":null,"reference_sig":null,"date_range":{"start":null,"end":null},"status":"","text":null,"product":{"name":"Lumigan","code":"","code_system":"","text":null,"translation":{"name":null,"code":null,"code_system":null,"code_system_name":null}},"dose_quantity":{"value":null,"unit":null},"rate_quantity":{"value":null,"unit":null},"precondition":{"name":null,"code":null,"code_system":null},"reason":{"name":null,"code":null,"code_system":null},"route":{"name":null,"code":null,"code_system":null,"code_system_name":null},"schedule":{"type":null,"period_value":null,"period_unit":null},"vehicle":{"name":null,"code":null,"code_system":null,"code_system_name":null},"administration":{"name":null,"code":null,"code_system":null,"code_system_name":null},"prescriber":{"organization":null,"person":null}},{"reference":null,"reference_title":null,"reference_sig":null,"date_range":{"start":null,"end":null},"status":"","text":null,"product":{"name":"Spiriva HandiHaler","code":"","code_system":"","text":null,"translation":{"name":null,"code":null,"code_system":null,"code_system_name":null}},"dose_quantity":{"value":null,"unit":null},"rate_quantity":{"value":null,"unit":null},"precondition":{"name":null,"code":null,"code_system":null},"reason":{"name":null,"code":null,"code_system":null},"route":{"name":null,"code":null,"code_system":null,"code_system_name":null},"schedule":{"type":null,"period_value":null,"period_unit":null},"vehicle":{"name":null,"code":null,"code_system":null,"code_system_name":null},"administration":{"name":null,"code":null,"code_system":null,"code_system_name":null},"prescriber":{"organization":null,"person":null}},{"reference":null,"reference_title":null,"reference_sig":null,"date_range":{"start":null,"end":null},"status":"","text":null,"product":{"name":"Furosemide","code":"","code_system":"","text":null,"translation":{"name":null,"code":null,"code_system":null,"code_system_name":null}},"dose_quantity":{"value":null,"unit":null},"rate_quantity":{"value":null,"unit":null},"precondition":{"name":null,"code":null,"code_system":null},"reason":{"name":null,"code":null,"code_system":null},"route":{"name":null,"code":null,"code_system":null,"code_system_name":null},"schedule":{"type":null,"period_value":null,"period_unit":null},"vehicle":{"name":null,"code":null,"code_system":null,"code_system_name":null},"administration":{"name":null,"code":null,"code_system":null,"code_system_name":null},"prescriber":{"organization":null,"person":null}},{"reference":null,"reference_title":null,"reference_sig":null,"date_range":{"start":null,"end":null},"status":"","text":null,"product":{"name":"Rosuvastatin","code":"","code_system":"","text":null,"translation":{"name":null,"code":null,"code_system":null,"code_system_name":null}},"dose_quantity":{"value":null,"unit":null},"rate_quantity":{"value":null,"unit":null},"precondition":{"name":null,"code":null,"code_system":null},"reason":{"name":null,"code":null,"code_system":null},"route":{"name":null,"code":null,"code_system":null,"code_system_name":null},"schedule":{"type":null,"period_value":null,"period_unit":null},"vehicle":{"name":null,"code":null,"code_system":null,"code_system_name":null},"administration":{"name":null,"code":null,"code_system":null,"code_system_name":null},"prescriber":{"organization":null,"person":null}}],"payers":[],"problems":[{"reference":null,"reference_title":null,"date_range":{"start":null,"end":null},"name":null,"status":null,"age":null,"code":"250.80","code_system":null,"code_system_name":"ICD9","translations":[{"name":null,"code":null,"code_system":null,"code_system_name":null}],"comment":null},{"reference":null,"reference_title":null,"date_range":{"start":null,"end":null},"name":null,"status":null,"age":null,"code":"272.2","code_system":null,"code_system_name":"ICD9","translations":[{"name":null,"code":null,"code_system":null,"code_system_name":null}],"comment":null},{"reference":null,"reference_title":null,"date_range":{"start":null,"end":null},"name":null,"status":null,"age":null,"code":"496","code_system":null,"code_system_name":"ICD9","translations":[{"name":null,"code":null,"code_system":null,"code_system_name":null}],"comment":null},{"reference":null,"reference_title":null,"date_range":{"start":null,"end":null},"name":null,"status":null,"age":null,"code":"401.9","code_system":null,"code_system_name":"ICD9","translations":[{"name":null,"code":null,"code_system":null,"code_system_name":null}],"comment":null},{"reference":null,"reference_title":null,"date_range":{"start":null,"end":null},"name":null,"status":null,"age":null,"code":"E11.329","code_system":null,"code_system_name":"ICD9","translations":[{"name":null,"code":null,"code_system":null,"code_system_name":null}],"comment":null},{"reference":null,"reference_title":null,"date_range":{"start":null,"end":null},"name":null,"status":null,"age":null,"code":"Z79.4","code_system":null,"code_system_name":"ICD9","translations":[{"name":null,"code":null,"code_system":null,"code_system_name":null}],"comment":null},{"reference":null,"reference_title":null,"date_range":{"start":null,"end":null},"name":null,"status":null,"age":null,"code":"C18.9","code_system":null,"code_system_name":"ICD9","translations":[{"name":null,"code":null,"code_system":null,"code_system_name":null}],"comment":null}],"vitals":[{"date":null,"results":[{"name":null,"code":null,"code_system":null,"code_system_name":null,"value":null,"unit":null}]}]}'
        );
    }
    
    public function test_it_creates_medical_record_from_array()
    {
        $mr = new CcdaMedicalRecord($this->getFakeMedicalRecord());
    
        $ccda = Ccda::create(
            [
                'source'      => $mr->getType(),
                'json'        => $mr->toJson(),
                'practice_id' => $this->patient()->program_id,
            ]
        );
        
        $args = [
            'patientUserId' => $this->patient()->id,
        ];
        
    try {
        $ccda->import();
    } catch (ValidationException $e) {
        $this->addWarning($e->validator->errors()->toJson());
    }
        
        $newPatient = $ccda->fresh()->patient()->firstOrFail();
        
        $this->assertTrue($ccda->patientFirstName() === $newPatient->first_name);
    }
}
