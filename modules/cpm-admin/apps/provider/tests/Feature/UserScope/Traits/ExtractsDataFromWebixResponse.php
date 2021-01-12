<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature\UserScope\Traits;

use Illuminate\Testing\TestResponse;

trait ExtractsDataFromWebixResponse
{
    private function extractWebixResponseData(TestResponse $response, string $webixDataVariableName)
    {
        $data = collect(json_decode(ltrim($this->extractResponseData($response)->get($webixDataVariableName), 'data:'), true));

        if ($data->isEmpty()) {
            throw new \Exception('Unreliable test: Webix data is empty, so there will be nothing to assert.');
        }

        return $data;
    }
}
