<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

return [
    'store_results_in_db' => env('CCDA_PARSER_STORE_RESULTS_IN_DB', true),
    'db_connection'       => env('CCDA_PARSER_DB_CONNECTION', 'mysql'),
    'db_table'            => env('CCDA_PARSER_DB_JSON_TABLE', 'ccdas-json'),
];
