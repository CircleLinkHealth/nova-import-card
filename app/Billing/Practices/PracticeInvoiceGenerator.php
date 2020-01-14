<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Billing\Practices;

use CircleLinkHealth\Core\PdfService;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;

class PracticeInvoiceGenerator
{
    private $month;
    private $patients;

    private $practice;

    /**
     * PracticeInvoiceGenerator constructor.
     *
     * @param Practice $practice
     * @param Carbon   $month
     */
    public function __construct(
        Practice $practice,
        Carbon $month
    ) {
        $this->practice = $practice;
        $this->month    = $month->firstOfMonth();
    }

    /**
     * @param bool $withItemized
     *
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     *
     * @return array
     */
    public function generatePdf($withItemized = true)
    {
        $invoiceName = trim($this->practice->name).'-'.$this->month->toDateString().'-invoice';

        $pdfInvoice = $this->makeInvoicePdf($invoiceName);

        $data = [
            'invoice_url' => $pdfInvoice->getUrl(),
        ];

        if ($withItemized) {
            $reportName       = trim($this->practice->name).'-'.$this->month->toDateString().'-patients';
            $pdfPatientReport = $this->makePatientReportPdf($reportName);

            $data['patient_report_url'] = $pdfPatientReport->getUrl();
        }

        $data['practiceId'] = $this->practice->id;

        return $data;
    }

    /**
     * @param $reportName
     *
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     *
     * @return \Spatie\MediaLibrary\Models\Media
     */
    public function makeInvoicePdf($reportName)
    {
        $pdfService = app(PdfService::class);

        \Storage::disk('storage')
            ->makeDirectory('download');

        $path = storage_path("download/${reportName}.pdf");
        $pdf  = $pdfService->createPdfFromView('billing.practice.invoice', $this->practice->getInvoiceData($this->month), $path);

        return $this->practice
            ->addMedia($path)
            ->toMediaCollection("invoice_for_{$this->month->toDateString()}");
    }

    /**
     * @param $reportName
     *
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     *
     * @return \Spatie\MediaLibrary\Models\Media
     */
    public function makePatientReportPdf($reportName)
    {
        $pdfService = app(PdfService::class);

        \Storage::disk('storage')
            ->makeDirectory('download');

        $path = storage_path("/download/${reportName}.pdf");

        $pdf = $pdfService->createPdfFromView('billing.practice.itemized', $this->practice->getItemizedPatientData($this->month), $path);

        return $this->practice
            ->addMedia($path)
            ->toMediaCollection("patient_report_for_{$this->month->toDateString()}");
    }
}
