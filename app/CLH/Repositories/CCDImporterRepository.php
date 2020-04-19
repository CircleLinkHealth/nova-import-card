<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\CLH\Repositories;

use GuzzleHttp\Client;

class CCDImporterRepository
{
    public function toBlueButtonJson($xml)
    {
        $client = new Client([
            'base_uri' => config('services.ccd-parser.base-uri'),
        ]);

        $response = $client->request('POST', '/api/parser', [
            'headers' => ['Content-Type' => 'text/xml'],
            'body'    => $xml,
        ]);

        $responseBody = (string) $response->getBody();

        if ( ! in_array($response->getStatusCode(), [200, 201])) {
            $data = json_encode([
                $response->getStatusCode(),
                $response->getReasonPhrase(),
            ]);

            throw new \Exception("Could not process ccd. Data: ${data}");
        }

        return $responseBody;
    }
}
