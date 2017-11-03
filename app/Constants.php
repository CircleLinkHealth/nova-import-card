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
}
