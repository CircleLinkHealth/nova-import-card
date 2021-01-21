<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\DTO;

use CircleLinkHealth\SelfEnrollment\Database\Seeders\GenerateCameronLetter;

class CameronLetterProductionValueObject
{
    const ANNNE_REITZ_MAIL_REAL         = 'areitz@cameronmch.com';
    const BRANDY_GERMAN_MAIL_REAL       = 'bgerman@cameronmch.com';
    const CHRISHAWVA_SCHIEBER_MAIL_REAL = 'cschieber@cameronmch.com';
    const FAUR_MAIL_REAL                = 'lfaur@cameronmch.com';
    const MILLER_MAIL_REAL              = 'tmiller@cameronmch.com';

    public function signatoryProvidersGroup()
    {
        return [
            [
                'first_name' => 'Thomas',
                'last_name'  => 'Miller',
                'email'      => self::MILLER_MAIL_REAL,
                'signature'  => GenerateCameronLetter::MILLER_SIGNATURE,
            ],

            [
                'first_name' => 'Lynn',
                'last_name'  => 'Faur',
                'email'      => self::FAUR_MAIL_REAL,
                'signature'  => GenerateCameronLetter::FAUR_SIGNATURE,
            ],

            [
                'first_name' => 'Brandy',
                'last_name'  => 'German',
                'email'      => self::BRANDY_GERMAN_MAIL_REAL,
                'signature'  => GenerateCameronLetter::MILLER_SIGNATURE,
            ],
            [
                'first_name' => 'Anne',
                'last_name'  => 'Reitz',
                'email'      => self::ANNNE_REITZ_MAIL_REAL,
                'signature'  => GenerateCameronLetter::MILLER_SIGNATURE,
            ],

            [
                'first_name' => 'Chrishawna',
                'last_name'  => 'Schieber',
                'email'      => self::CHRISHAWVA_SCHIEBER_MAIL_REAL,
                'signature'  => GenerateCameronLetter::FAUR_SIGNATURE,
            ],
        ];
    }
}
