<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\PdfService\Services;

use Carbon\Carbon;
use CircleLinkHealth\Core\Exceptions\FileNotFoundException;
use File;
use Illuminate\Support\Str;
use LynX39\LaraPdfMerger\PdfManage;

class PdfService
{
    private HtmlToPdfService $htmlToPdfService;

    public function __construct(HtmlToPdfService $htmlToPdfService)
    {
        $this->htmlToPdfService = $htmlToPdfService;
    }

    /**
     * Create a blank page.
     *
     * @return string
     */
    public function blankPage(string $fileName = null)
    {
        if ( ! $fileName) {
            $fileName = 'blank_page.pdf';
        }
        $blankPagePath = $this->getPath("pdfs/$fileName");

        if (file_exists($blankPagePath)) {
            return $blankPagePath;
        }

        $pdf = $this->htmlToPdfService
            ->loadHTML('<div></div>')
            ->save($blankPagePath, true);

        return $blankPagePath;
    }

    /**
     * Count pages of a PDF.
     *
     * @param $pdfFullPath
     *
     * @return false|int
     */
    public function countPages($pdfFullPath)
    {
        $pdftext = file_get_contents($pdfFullPath);

        return preg_match_all('/\\/Page\\W/', $pdftext, $dummy);
    }

    public function createPdfFromHtml(string $html, $outputFullPath = null, array $options = [])
    {
        $pdf = $this->htmlToPdfService->loadHTML($html);
        $this->setOptions($pdf, $options);
        $pdf->save($outputFullPath, true);

        if ( ! file_exists($outputFullPath)) {
            throw new FileNotFoundException("File not found at `$outputFullPath`. Seems like PDF was not generated.");
        }

        return $outputFullPath;
    }

    /**
     * Create a PDF from a View.
     *
     * @param $view
     * @param null $outputFullPath
     *
     * @throws \Exception
     * @return string|null
     */
    public function createPdfFromView($view, array $args, $outputFullPath = null, array $options = [])
    {
        if ( ! $outputFullPath) {
            $outputFullPath = $this->randomFileFullPath();
        }

        if ( ! array_key_exists('isPdf', $args)) {
            $args['isPdf'] = true;
        }

        //if a user's careplan is mode=pdf we might have a pdf already. so no need to generate
        //check that pdfCareplan is provided with $args and is not null
        $args['generatePdfCareplan'] = empty($args['pdfCareplan']);

        $pdf = $this->htmlToPdfService->loadView($view, $args);
        $this->setOptions($pdf, $options);
        $pdf->save($outputFullPath, true);

        if ( ! $args['generatePdfCareplan']) {
            $outputFullPath = $this->mergeFiles(
                [
                    $outputFullPath,
                    $this->getPath("patient/pdf-careplans/{$args['pdfCareplan']->filename}"),
                ]
            );
        }

        if ( ! file_exists($outputFullPath)) {
            throw new FileNotFoundException("File not found at `$outputFullPath`. Seems like PDF was not generated.");
        }

        return $outputFullPath;
    }

    /**
     * Merge an array of files.
     * NOTE: Each index in the array has to be a full path to a file.
     *
     * @param null $outputFullPath
     *
     * @throws \Exception
     * @return string|null
     */
    public function mergeFiles(
        array $filesWithFullPath,
        $outputFullPath = null
    ) {
        if ( ! $outputFullPath) {
            $outputFullPath = $this->randomFileFullPath();
        }

        $this->resolvePath($outputFullPath);

        $pdf = new PdfManage();
        $pdf->init();
        foreach ($filesWithFullPath as $file) {
            $pdf->addPDF($file, 'all');
        }

        $pdf->merge('P');
        $pdf->save($outputFullPath);

        return $outputFullPath;
    }

    private function getPath(string $relative = null)
    {
        return storage_path($relative);
    }

    /**
     * Generate a random file full path.
     *
     * @return string
     */
    private function randomFileFullPath()
    {
        $name = Carbon::now()->toAtomString().Str::random(20);

        return $this->getPath("pdfs/${name}.pdf");
    }

    private function resolvePath(string $path)
    {
        $folder = dirname($path);
        if ( ! File::isDirectory($folder)) {
            File::makeDirectory($folder);
        }
    }

    private function setOptions(HtmlToPdfService $pdf, array $options = [])
    {
        if ( ! empty($options)) {
            foreach ($options as $key => $value) {
                $pdf = $pdf->setOption($key, $value);
            }
        } else {
            $pdf->setOption('displayHeaderFooter', true)
                ->setOption('footerTemplate', '<div style="margin: auto; text-align: center; font-size: 10px;">Page <span class="pageNumber"></span></div>')
                ->setOption('margin', [
                    'top'    => '8mm',
                    'left'   => '8mm',
                    'bottom' => '8mm',
                    'right'  => '8mm',
                ]);
        }
    }
}
