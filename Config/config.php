<?php

return [
    'store_results_in_db' => env('CCDA_PARSER_STORE_RESULTS_IN_DB', false),
    'db_connection'       => env('CCDA_PARSER_DB_CONNECTION', 'mysql'),
    'db_table'            => env('CCDA_PARSER_DB_JSON_TABLE', 'ccdas'),
];