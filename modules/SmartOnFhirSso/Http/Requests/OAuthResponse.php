<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SmartOnFhirSso\Http\Requests;

class OAuthResponse
{
    public ?string $accessToken;
    public ?string $encounter;
    public ?int $expiresInSecs;
    public ?string $location;
    public ?string $openIdToken;
    public ?string $patientDstu2FhirId;
    public ?string $patientFhirId;
    public ?string $scope;
    public ?string $state;
    public ?string $tokenType;

    public function __construct(?array $arr)
    {
        if (empty($arr)) {
            return;
        }

        $this->accessToken        = $arr['access_token'] ?? null;
        $this->tokenType          = $arr['token_type'] ?? null;
        $this->expiresInSecs      = $arr['expires_in'] ?? null;
        $this->scope              = $arr['scope'] ?? null;
        $this->openIdToken        = $arr['id_token'] ?? null;
        $this->patientFhirId      = $arr['patient'] ?? null;
        $this->patientDstu2FhirId = $arr['epic.dstu2.patient'] ?? null;
        $this->encounter          = $arr['encounter'] ?? null;
        $this->location           = $arr['location'] ?? null;
        $this->state              = $arr['state'] ?? null;
    }
}
