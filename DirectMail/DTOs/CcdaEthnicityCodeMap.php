<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\DirectMail\DTOs;

class CcdaEthnicityCodeMap
{
    public const TEXT_TO_CODE = [
        'asian'                     => '2028-9',
        'black or african american' => '2054-5',
        'hispanic or latino'        => '2135-2',
        'native'                    => '2076-8',
        'not hispanic or latino'    => '2186-5',
        'other'                     => '2131-1',
        'white'                     => '2106-3',
    ];

    public static function codeFromText(?string $ethnicity): string
    {
        return self::TEXT_TO_CODE[strtolower($ethnicity)] ?? '';
    }
}
