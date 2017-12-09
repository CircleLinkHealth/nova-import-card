<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 10/24/2017
 * Time: 12:03 PM
 */

namespace App;

class Constants
{
    /**
     * Redis Cache Keys
     */
    const CACHED_USER_NOTIFICATIONS = 'user:{$userId}:notifications';

    /**
     * Problem Codes
     */
    const ICD9 = 'icd_9_code';
    const ICD10 = 'icd_10_code';
    const SNOMED = 'snomed_code';

    const ICD9_NAME = 'ICD-9';
    const ICD10_NAME = 'ICD-10';
    const SNOMED_NAME = 'SNOMED CT';

    const CODE_SYSTEM_NAME_ID_MAP = [
        Constants::ICD9_NAME   => 1,
        Constants::ICD10_NAME  => 2,
        Constants::SNOMED_NAME => 3,
    ];
}
