<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ResponseCache;

use CircleLinkHealth\ResponseCache\Exceptions\CouldNotUnserialize;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ResponseSerializer
{
    const RESPONSE_TYPE_FILE   = 'response_type_file';
    const RESPONSE_TYPE_NORMAL = 'response_type_normal';

    public function serialize(Response $response): string
    {
        return serialize($this->getResponseData($response));
    }

    public function unserialize(string $serializedResponse): Response
    {
        $responseProperties = unserialize($serializedResponse);

        if ( ! is_array($responseProperties)) {
            $responseProperties = $this->unserialize($responseProperties);

            if (is_a($responseProperties, Response::class)) {
                return $responseProperties;
            }
        }

        if ( ! $this->containsValidResponseProperties($responseProperties)) {
            throw CouldNotUnserialize::serializedResponse($serializedResponse);
        }

        $response = $this->buildResponse($responseProperties);

        $response->headers = $responseProperties['headers'];

        return $response;
    }

    protected function buildResponse(array $responseProperties): Response
    {
        $type = $responseProperties['type'] ?? self::RESPONSE_TYPE_NORMAL;

        if (self::RESPONSE_TYPE_FILE === $type) {
            return new BinaryFileResponse(
                $responseProperties['content'],
                $responseProperties['statusCode']
            );
        }

        return new Response($responseProperties['content'], $responseProperties['statusCode']);
    }

    protected function containsValidResponseProperties($properties): bool
    {
        if ( ! is_array($properties)) {
            return false;
        }

        if ( ! isset($properties['content'], $properties['statusCode'])) {
            return false;
        }

        return true;
    }

    protected function getResponseData(Response $response): array
    {
        $statusCode = $response->getStatusCode();
        $headers    = $response->headers;

        if ($response instanceof BinaryFileResponse) {
            $content = $response->getFile()->getPathname();
            $type    = self::RESPONSE_TYPE_FILE;

            return compact('statusCode', 'headers', 'content', 'type');
        }

        $content = $response->getContent();
        $type    = self::RESPONSE_TYPE_NORMAL;

        return compact('statusCode', 'headers', 'content', 'type');
    }
}
