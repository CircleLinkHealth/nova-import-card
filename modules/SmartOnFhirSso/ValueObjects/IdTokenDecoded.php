<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SmartOnFhirSso\ValueObjects;

class IdTokenDecoded
{
    public string $alg;
    public string $aud;
    public int $exp;
    public string $fhirUser;
    public int $iat;
    public string $iss;
    public string $profile;
    public string $sig;
    public string $sub;
    public string $typ;

    public function __construct(array $object)
    {
        $this->typ      = $object[0]['typ'];
        $this->alg      = $object[0]['alg'];
        $this->profile  = $object[1]['profile'];
        $this->fhirUser = $object[1]['fhirUser'];
        $this->iss      = $object[1]['iss'];
        $this->aud      = $object[1]['aud'];
        $this->sub      = $object[1]['sub'];
        $this->iat      = $object[1]['iat'];
        $this->exp      = $object[1]['exp'];
        $this->sig      = $object[2];
    }
}
