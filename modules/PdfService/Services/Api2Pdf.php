<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\PdfService\Services;

use Api2Pdf\Exception\ConversionException;
use Api2Pdf\Exception\ProtocolException;
use File;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class Api2Pdf implements HtmlToPdfService
{
    private ?string $htmlString;
    private array $options;

    /**
     * Api2Pdf constructor.
     */
    public function __construct()
    {
        $this->htmlString = null;
        $this->options    = [];
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

    /**
     * @throws ConversionException
     * @throws ProtocolException
     */
    public function save(string $filename, bool $overwrite = false): HtmlToPdfService
    {
        $api = new \Api2Pdf\Api2Pdf(config('services.api2pdf.api-key'));
        try {
            $result = $api->wkHtmlToPdfFromHtml($this->htmlString);
        } catch (ConversionException|ProtocolException $e) {
            throw $e;
        }

        $this->resolvePath($filename);

        Log::debug("Saving pdf to $filename");
        $pdfLink = $result->getPdf();
        Log::debug("Pdf link: $pdfLink");
        file_put_contents($filename, (new Client())->get($pdfLink)->getBody());

        return $this;
    }

    public function setOption(string $name, $value): HtmlToPdfService
    {
        // TODO: Implement setOption() method.
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
