<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\PdfService\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class ServerlessPdfService implements HtmlToPdfService
{
    private Client $client;
    private ?string $htmlString;
    private array $options;
    private string $url;
    
    /**
     * ServerlessPdfService constructor.
     */
    public function __construct()
    {
        $this->url          = config('services.serverless-pdf-generator.api-url');
        $this->client = new Client([
            'base_uri' => $this->url,
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
        $optionsStr = json_encode($this->options);
        Log::debug("Calling: $this->url with options: $optionsStr");

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

        resolvePath($filename);
        Log::debug("Saving pdf to $filename");
        file_put_contents($filename.'.html', $this->htmlString);
        file_put_contents($filename, $body);

        return $this;
    }

    public function setOption(string $name, $value): HtmlToPdfService
    {
        $this->options[$name] = $value;

        return $this;
    }
}
