<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 01/11/2018
 * Time: 12:02 AM
 */

namespace App\Services;


use App\Contracts\HtmlToPdfService;
use Carbon\Carbon;

class PdfService
{
    private $htmlToPdfService;

    public function __construct(HtmlToPdfService $htmlToPdfService)
    {
        $this->htmlToPdfService = $htmlToPdfService->handler();
    }

    /**
     * Merge an array of files.
     * NOTE: Each index in the array has to be a full path to a file
     *
     * @param array $filesWithFullPath
     * @param null $outputFullPath
     *
     * @return null|string
     */
    public function mergeFiles(
        array $filesWithFullPath,
        $outputFullPath = null
    ) {
        if ( ! $outputFullPath) {
            $outputFullPath = $this->randomFileFullPath();
        }

        $cmd = "gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=\"$outputFullPath\" ";

        //Add each pdf file to the end of the command
        foreach ($filesWithFullPath as $file) {
            $cmd .= '"' . $file . '" ';
        }
        $result = shell_exec($cmd);

        return $outputFullPath;
    }

    /**
     * Generate a random file full path
     *
     * @return string
     */
    private function randomFileFullPath()
    {
        $name = str_random() . Carbon::now()->toAtomString();

        return storage_path("pdfs/$name.pdf") ;
    }

    /**
     * Count pages of a PDF
     *
     * @param $pdfFullPath
     *
     * @return false|int
     */
    public function countPages($pdfFullPath)
    {
        $pdftext = file_get_contents($pdfFullPath);
        $num     = preg_match_all("/\/Page\W/", $pdftext, $dummy);

        return $num;
    }

    /**
     * Create a PDF from a View
     *
     * @param $view
     * @param array $args
     * @param null $outputFullPath
     *
     * @return null|string
     */
    public function createPdfFromView($view, array $args, $outputFullPath = null)
    {
        if ( ! $outputFullPath) {
            $outputFullPath = $this->randomFileFullPath();
        }

        if (!array_key_exists('isPdf', $args)) {
            $args['isPdf'] = true;
        }
//            leaving these here in case we need them
//            $pdf->setOption('disable-javascript', false);
//            $pdf->setOption('enable-javascript', true);
//            $pdf->setOption('javascript-delay', 400);

        $pdf = $this->htmlToPdfService
            ->loadView($view, $args)
            ->setOption('footer-center', 'Page [page]')
            ->setOption('margin-top', '12')
            ->setOption('margin-left', '25')
            ->setOption('margin-bottom', '15')
            ->setOption('margin-right', '0.75');

        if (isset($args['pdfOptions'])) {
            foreach ($args['pdfOptions'] as $key => $value) {
                $pdf = $pdf->setOption($key, $value);
            }
        }
        
        $pdf = $pdf->save($outputFullPath, true);

        return $outputFullPath;
    }

    /**
     * Create a blank page
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
}