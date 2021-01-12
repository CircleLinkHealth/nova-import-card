<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Tests\Fakers;

class AthenaApiResponses
{
    public static function failGetCcd()
    {
        return [];
    }

    public static function getCcd()
    {
        return [
            [
                'ccda' => file_get_contents(storage_path('ccdas/Samples/demo.xml')),
            ],
        ];
    }

    public static function getPatientInsurances()
    {
        return [
            'insurances' => [
                [
                    'insurancepolicyholdercountrycode' => 'USA',
                    'sequencenumber'                   => '1',
                    'insurancepolicyholderlastname'    => 'DOE',
                    'insuredentitytypeid'              => '1',
                    'insuranceidnumber'                => '123456TEST',
                    'insurancepolicyholderstate'       => 'NJ',
                    'insurancepolicyholderzip'         => '07666',
                    'insurancepolicyholderdob'         => "01\/01\/1950",
                    'issuedate'                        => "01\/01\/2019",
                    'relationshiptoinsured'            => 'Self',
                    'eligibilitystatus'                => 'Eligible',
                    'policynumber'                     => '54321',
                    'insurancepolicyholderaddress1'    => '123 SUMMER ST',
                    'insurancepackageaddress1'         => '',
                    'insurancepolicyholdersex'         => 'F',
                    'eligibilityreason'                => 'Athena',
                    'ircname'                          => 'TEST',
                    'insuranceplanname'                => 'TEST HEALTHCARE',
                    'insurancetype'                    => 'Commercial',
                    'insurancephone'                   => '(800) 882-4462',
                    'insurancepackagestate'            => 'NJ',
                    'insurancepackagecity'             => 'TEANECK',
                    'relationshiptoinsuredid'          => '1',
                    'insuranceid'                      => '1111',
                    'insurancepolicyholder'            => 'JANE DOE',
                    'eligibilitylastchecked'           => "08\/01\/2019",
                    'copays'                           => [[
                        'copayamount' => '100',
                        'copaytype'   => 'Office Visit',
                    ]],
                    'insurancepolicyholdermiddlename'     => 'X',
                    'insurancepolicyholderfirstname'      => 'JANE',
                    'insurancepackageid'                  => '99',
                    'insurancepolicyholdercountryiso3166' => 'US',
                    'insuranceplandisplayname'            => 'TEST',
                    'eligibilitymessage'                  => 'Member is eligible.',
                    'insurancepolicyholdercity'           => 'TEANECK',
                    'insurancepackagezip'                 => '07666',
                ],
            ],
            'totalcount' => 1,
        ];
    }
}
