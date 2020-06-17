<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Services;

use Carbon\Carbon;
use CircleLinkHealth\Core\Exceptions\FileNotFoundException;
use Illuminate\Support\Str;
use LynX39\LaraPdfMerger\PdfManage;

class PdfService
{
    private $htmlToPdfService;

    public function __construct(HtmlToPdfService $htmlToPdfService)
    {
        $this->htmlToPdfService = $htmlToPdfService->handler();
    }

    /**
     * Create a blank page.
     *
     * @return string
     */
    public function blankPage()
    {
        $blankPagePath = storage_path('pdfs/blank_page.pdf');

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

    /**
     * Create a PDF from a View.
     *
     * @param $view
     * @param null $outputFullPath
     *
     * @throws \Exception
     *
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

//            leaving these here in case we need them
//            $pdf->setOption('disable-javascript', false);
//            $pdf->setOption('enable-javascript', true);
//            $pdf->setOption('javascript-delay', 400);

        $pdf = $this->htmlToPdfService
            ->loadView($view, $args);
        if ( ! empty($options)) {
            foreach ($options as $key => $value) {
                $pdf = $pdf->setOption($key, $value);
            }
        } else {
            $pdf->setOption('footer-center', 'Page [page]')
                ->setOption('margin-top', '8')
                ->setOption('margin-left', '8')
                ->setOption('margin-bottom', '8')
                ->setOption('margin-right', '8');
        }

        $pdf = $pdf->save($outputFullPath, true);

        if ( ! $args['generatePdfCareplan']) {
            $outputFullPath = $this->mergeFiles(
                [
                    $outputFullPath,
                    storage_path("patient/pdf-careplans/{$args['pdfCareplan']->filename}"),
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
     *
     * @return string|null
     */
    public function mergeFiles(
        array $filesWithFullPath,
        $outputFullPath = null
    ) {
        if ( ! $outputFullPath) {
            $outputFullPath = $this->randomFileFullPath();
        }

        $pdf = new PdfManage();
        $pdf->init();
        foreach ($filesWithFullPath as $file) {
            $pdf->addPDF($file, 'all');
        }

        $pdf->merge('P');
        $pdf->save($outputFullPath);

        return $outputFullPath;
    }

    /**
     * Generate a random file full path.
     *
     * @return string
     */
    private function randomFileFullPath()
    {
        $name = Carbon::now()->toAtomString().Str::random(20);

        return storage_path("pdfs/${name}.pdf");
    }
}
