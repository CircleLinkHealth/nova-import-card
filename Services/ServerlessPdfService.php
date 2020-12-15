<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\PdfService\Services;

use File;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class ServerlessPdfService implements HtmlToPdfService
{
    private Client $client;
    private ?string $htmlString;
    private array $options;

    /**
     * ServerlessPdfService constructor.
     */
    public function __construct()
    {
        $url          = config('services.serverless-pdf-generator.api-url');
        $this->client = new Client([
            'base_uri' => $url,
            'headers'  => [
                'x-api-key' => config('services.serverless-pdf-generator.api-key'),
            ],
        ]);
        $this->htmlString = null;
        $this->options    = config('services.serverless-pdf-generator.default-options') ?? [];
    }

    public function loadHTML(string $htmlString): HtmlToPdfService
    {
        $this->htmlString = $htmlString;

        return $this;
    }

    public function loadView(string $viewName, array $args): HtmlToPdfService
    {
        $view = View::make($viewName, $args);
        $this->loadHTML($view->render());

        return $this;
    }

    public function save(string $filename, bool $overwrite = false): HtmlToPdfService
    {
        $url = $this->client->getConfig('base_uri');
        Log::debug("Calling: $url");

        $result = $this->client->post(
            '',
            [
                'json' => [
                    'html'     => $this->htmlString,
                    'fileName' => basename($filename),
                    'options'  => $this->options,
                ],
            ]
        );
        $body = $result->getBody();
        if (200 !== $result->getStatusCode()) {
            throw new \Exception($body);
        }

        $this->resolvePath($filename);
        file_put_contents($filename, $body);
        Log::debug("Saving pdf to $filename");

        return $this;
    }

    public function setOption(string $name, $value): HtmlToPdfService
    {
        $this->options[$name] = $value;

        return $this;
    }

    private function resolvePath(string $path)
    {
        $folder = dirname($path);
        if ( ! File::isDirectory($folder)) {
            File::makeDirectory($folder);
        }
    }
}
