<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Laravel\Vapor\Runtime\Http;

use Illuminate\Support\Arr as SupportArr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Stream;
use Nyholm\Psr7\UploadedFile;
use Riverline\MultiPartParser\Part;

class PsrRequestFactory
{
    /**
     * The incoming Lambda event payload array.
     *
     * @var array
     */
    protected $event;

    /**
     * Create a new invokable request factory class instance.
     */
    public function __construct(array $event)
    {
        $this->event = $event;
    }

    /**
     * Create a new PSR-7 request.
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function __invoke()
    {
        $headers = $this->headers();

        $queryString = $this->queryString();

        parse_str($queryString, $query);

        $serverRequest = new ServerRequest(
            $this->method(),
            $this->uri(),
            $headers,
            $this->bodyStream(),
            $this->protocolVersion(),
            $this->serverVariables($headers, $queryString)
        );

        $serverRequest = $serverRequest->withQueryParams($query);
        $serverRequest = $serverRequest->withCookieParams($this->cookies($headers));
        $serverRequest = $serverRequest->withParsedBody($this->parsedBody($headers));

        return $serverRequest->withUploadedFiles($this->uploadedFiles($headers));
    }

    /**
     * Get the HTTP request body stream.
     *
     * @return \Psr\Http\Message\StreamInterface
     */
    protected function bodyStream()
    {
        return Stream::create($this->bodyString());
    }

    /**
     * Get the raw body string for the event.
     *
     * @return string
     */
    protected function bodyString()
    {
        return $this->event['body'] ?? '';
    }

    /**
     * Get the Content-Type header for the event.
     *
     * @return string|null
     */
    protected function contentType()
    {
        return array_change_key_case($this->headers())['content-type'] ?? null;
    }

    /**
     * Get the cookies for the event.
     *
     * @return array
     */
    protected function cookies(array $headers)
    {
        $headers = array_change_key_case($headers);

        if ( ! isset($headers['cookie'])) {
            return [];
        }

        return Collection::make(explode('; ', $headers['cookie']))->mapWithKeys(function ($cookie) {
            [$key, $value] = explode('=', trim($cookie), 2);

            return [$key => urldecode($value)];
        })->all();
    }

    /**
     * Create a new file instance from the given HTTP request document part.
     *
     * @param  \Riverline\MultipartParser\Part         $part
     * @return \Psr\Http\Message\UploadedFileInterface
     */
    protected function createFile($part)
    {
        file_put_contents(
            $path = tempnam(sys_get_temp_dir(), 'vapor_upload_'),
            $part->getBody()
        );

        return new UploadedFile(
            $path,
            filesize($path),
            UPLOAD_ERR_OK,
            $part->getFileName(),
            $part->getMimeType()
        );
    }

    /**
     * Get the HTTP headers for the event.
     *
     * @return array
     */
    protected function headers()
    {
        return $this->event['headers'] ?? [];
    }

    /**
     * Get the HTTP method for the event.
     *
     * @return string
     */
    protected function method()
    {
        return $this->event['httpMethod'] ?? 'GET';
    }

    /**
     * Parse the incoming event's request body.
     *
     * @return array
     */
    protected function parseBody(string $contentType, string $body)
    {
        $document = new Part("Content-Type: $contentType\r\n\r\n".$body);

        if ( ! $document->isMultiPart()) {
            return;
        }

        return Collection::make($document->getParts())
            ->reject
            ->isFile()
            ->reduce(function ($parsedBody, $part) {
                return Str::contains($name = $part->getName(), '[')
                            ? Arr::setMultiPartArrayValue($parsedBody, $name, $part->getBody())
                            : SupportArr::set($parsedBody, $name, $part->getBody());
            }, []);
    }

    /**
     * Get the parsed body for the event.
     *
     * @return array|null
     */
    protected function parsedBody(array $headers)
    {
        if ('POST' !== $this->method() || is_null($contentType = $this->contentType())) {
            return;
        }

        $body = $this->bodyString();

        if ('application/x-www-form-urlencoded' === strtolower($contentType)) {
            parse_str($body, $parsedBody);

            return $parsedBody;
        }

        return $this->parseBody($contentType, $body);
    }

    /**
     * Parse the files for the given HTTP request body.
     *
     * @return array
     */
    protected function parseFiles(string $contentType, string $body)
    {
        $document = new Part("Content-Type: $contentType\r\n\r\n".$body);

        if ( ! $document->isMultiPart()) {
            return [];
        }

        return Collection::make($document->getParts())
            ->filter
            ->isFile()
            ->reduce(function ($files, $part) {
                return Str::contains($name = $part->getName(), '[')
                            ? Arr::setMultiPartArrayValue($files, $name, $this->createFile($part))
                            : SupportArr::set($files, $name, $this->createFile($part));
            }, []);
    }

    /**
     * Get the HTTP protocol version for the event.
     *
     * @return string
     */
    protected function protocolVersion()
    {
        return $this->event['requestContext']['protocol'] ?? '1.1';
    }

    /**
     * Get the query string for the event.
     *
     * @return string
     */
    protected function queryString()
    {
        return http_build_query($this->event['queryStringParameters'] ?? []);
    }

    /**
     * Get the server variables for the event.
     *
     * @return array
     */
    protected function serverVariables(array $headers, string $queryString)
    {
        $variables = [
            'HTTPS'              => 'on',
            'SERVER_PROTOCOL'    => $this->protocolVersion(),
            'REQUEST_METHOD'     => $this->method(),
            'REQUEST_TIME'       => $this->event['requestContext']['requestTimeEpoch'] ?? time(),
            'REQUEST_TIME_FLOAT' => microtime(true),
            'QUERY_STRING'       => $queryString,
            'DOCUMENT_ROOT'      => getcwd(),
            'REQUEST_URI'        => $this->uri(),
        ];

        if (isset($headers['Host'])) {
            $variables['HTTP_HOST'] = $headers['Host'];
        }

        return $variables;
    }

    /**
     * Get the uploaded files for the incoming event.
     *
     * @return array
     */
    protected function uploadedFiles(array $headers)
    {
        if ('POST' !== $this->method() ||
            is_null($contentType = $this->contentType()) ||
            'application/x-www-form-urlencoded' === $contentType) {
            return [];
        }

        return $this->parseFiles($contentType, $this->bodyString());
    }

    /**
     * Get the URI for the event.
     *
     * @return string
     */
    protected function uri()
    {
        return $this->event['requestContext']['path'] ?? '/';
    }
}
