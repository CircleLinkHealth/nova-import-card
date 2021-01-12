<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

return [
    'name'                  => 'SamlSp',
    'dump_acs_saml_request' => env('SAML2_DUMP_ACS_REQUEST', false),
    'dump_sls_saml_request' => env('SAML2_DUMP_SLS_REQUEST', false),
];
