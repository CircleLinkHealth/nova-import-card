<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SamlSp\Exceptions;

class SamlAuthenticationException extends \Illuminate\Auth\AuthenticationException
{
    protected $redirectTo = '/saml2/not-auth';
}
