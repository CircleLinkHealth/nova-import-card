<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

return [
    'name'     => 'ConditionCodeLookup',
    'api_urls' => [
        'icd9' => [
            'https://clinicaltables.nlm.nih.gov/api/icd9cm_dx/v3/search',
        ],
        'icd10' => [
            'https://clinicaltables.nlm.nih.gov/api/icd10cm/v3/search',
        ],
        'snomed' => [
        ],
    ],
    'cache' => [
        'enable' => env('CCL_CACHE_ENABLE', false),
    ],
];
