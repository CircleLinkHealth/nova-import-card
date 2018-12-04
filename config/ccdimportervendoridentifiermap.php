<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

//            'class' => ,
//            'name' => '',
//            'description' => '',
//        ],

return [
    'custodian_name' => [
        [
            'class'       => App\CLH\CCD\Identifier\IdentificationStrategies\CustodianName::class,
            'name'        => 'Custodian Name',
            'description' => 'Whatever is listed for Custodian Name on the CCD Viewer. It is in the Document section.',
        ],
    ],

    'doctor_name' => [
        [
            'class'       => App\CLH\CCD\Identifier\IdentificationStrategies\AuthorName::class,
            'name'        => 'Author Name',
            'description' => 'The doctor\'s full name with a space in between. eg. "John Doe".',
        ],
    ],

    'doctor_oid' => [
        [
            'class'       => App\CLH\CCD\Identifier\IdentificationStrategies\RepresentedOrganizationDoctorId::class,
            'name'        => 'Doctor Oid',
            'description' => 'UPG CCDs have this. eg. 2.16.840.1.113883.3.638 => Aprima,
                                2.16.840.1.113883.3.638.999 => Aprima with client Id',
        ],
        [
            'class'       => App\CLH\CCD\Identifier\IdentificationStrategies\NPI::class,
            'name'        => 'NPI',
            'description' => 'National Provider Identification (NPI)',
        ],
    ],

    'ehr_oid' => [
        [
            'class'       => \App\CLH\CCD\Identifier\IdentificationStrategies\RepresentedOrganizationId::class,
            'name'        => 'Represented Organization Id',
            'description' => 'The EHR\'s id. eg. For Aprima it is 638.',
        ],
    ],
];
