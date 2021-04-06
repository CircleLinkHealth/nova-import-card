<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

return [
    'name'                       => 'SmartOnFhirSso',
    'epic_app_client_id'         => env('EPIC_APP_CLIENT_ID', ''),
    'epic_app_staging_client_id' => env('EPIC_APP_STAGING_CLIENT_ID', ''),
    'routes_middleware'          => 'saml',
];
