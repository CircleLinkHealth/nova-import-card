<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Helpers;

use App\SafeRequest;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

trait MakesSafeRequests
{
    protected function safeRequest($uri, $method, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $symfonyRequest = SymfonyRequest::create(
            $this->prepareUrlForRequest($uri),
            $method,
            $parameters,
            $cookies,
            $files,
            array_replace($this->serverVariables, $server),
            $content
        );

        return SafeRequest::createFromBase($symfonyRequest);
    }
}
