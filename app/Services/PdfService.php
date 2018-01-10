<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 01/11/2018
 * Time: 12:02 AM
 */

namespace App\Services;


use App\Contracts\HtmlToPdfService;

class PdfService
{
    private $htmlToPdfService;

    public function __construct(HtmlToPdfService $htmlToPdfService)
    {
        $this->htmlToPdfService = $htmlToPdfService->handler();
    }

    public function mergeFiles(
        array $fileArray,
        $prefix = '',
        $storageDirectory = ''
    ) {
        $outputFileName = $prefix . "-merged.pdf";
        $outputName     = base_path($storageDirectory . $prefix . "-merged.pdf");

        $cmd = "gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=$outputName ";

        //Add each pdf file to the end of the command
        foreach ($fileArray as $file) {
            $cmd .= base_path($file) . " ";
        }
        $result = shell_exec($cmd);

        return $outputFileName;
    }

    public function countPages($pdfFullPath)
    {
        $pdftext = file_get_contents($pdfFullPath);
        $num     = preg_match_all("/\/Page\W/", $pdftext, $dummy);

        return $num;
    }

    public function createPdf($view, array $args, $fileFullPath)
    {
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
            ->setOption('margin-right', '0.75')
            ->save($fileFullPath, true);

        return $fileFullPath;
    }
}