<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\DTO;

use CircleLinkHealth\SelfEnrollment\Database\Seeders\GenerateCameronLetter;

class CameronLetterTestValueObject
{
    const ANNNE_REITZ_MAIL_TEST         = 'AnneTouReitz@example.com';
    const BRANDY_GERMAN_MAIL_TEST       = 'brandyToGermanou@example.com';
    const CHRISHAWVA_SCHIEBER_MAIL_TEST = 'chrishawnaTouSchieber@example.com';
    const FAUR_MAIL_TEST                = 'lyunToufaur@example.com';
    const MILLER_MAIL_TEST              = 'tomasTouMiller@example.com';

    public function testingData(int $practiceId)
    {
        return [
            [
                'first_name' => 'Thomas',
                'last_name'  => 'Miller',
                'email'      => self::MILLER_MAIL_TEST,
                'program_id' => $practiceId,
                'signature'  => GenerateCameronLetter::MILLER_SIGNATURE,
            ],

            [
                'first_name' => 'Lynn',
                'last_name'  => 'Faur',
                'email'      => self::FAUR_MAIL_TEST,
                'program_id' => $practiceId,
                'signature'  => GenerateCameronLetter::FAUR_SIGNATURE,
            ],

            [
                'first_name' => 'Brandy',
                'last_name'  => 'German',
                'email'      => self::BRANDY_GERMAN_MAIL_TEST,
                'program_id' => $practiceId,
                'signature'  => GenerateCameronLetter::MILLER_SIGNATURE,
            ],
            [
                'first_name' => 'Anne',
                'last_name'  => 'Reitz',
                'email'      => self::ANNNE_REITZ_MAIL_TEST,
                'program_id' => $practiceId,
                'signature'  => GenerateCameronLetter::MILLER_SIGNATURE,
            ],

            [
                'first_name' => 'Chrishawna',
                'last_name'  => 'Schieber',
                'email'      => self::CHRISHAWVA_SCHIEBER_MAIL_TEST,
                'program_id' => $practiceId,
                'signature'  => GenerateCameronLetter::FAUR_SIGNATURE,
            ],
        ];
    }
}
