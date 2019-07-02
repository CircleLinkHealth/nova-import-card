<?php

namespace CircleLinkHealth\ResponseCache\Replacers;

use CircleLinkHealth\ResponseCache\Replacers\Replacer;
use Symfony\Component\HttpFoundation\Response;

class CsrfTokenReplacer implements Replacer
{
    protected $replacementString = '<laravel-responsecache-csrf-token-here>';

    public function prepareResponseToCache(Response $response): void
    {
        if (! $response->getContent()) {
            return;
        }

        $response->setContent(str_replace(
            csrf_token(),
            $this->replacementString,
            $response->getContent()
        ));
    }

    public function replaceInCachedResponse(Response $response): void
    {
        if (! $response->getContent()) {
            return;
        }

        $response->setContent(str_replace(
            $this->replacementString,
            csrf_token(),
            $response->getContent()
        ));
    }
}
