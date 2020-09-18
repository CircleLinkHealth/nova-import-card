<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Services;

use Carbon\Carbon;
use CircleLinkHealth\Core\Exports\FromArray;
use CircleLinkHealth\Core\Services\PdfService;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Invoices\ItemizedBillablePatientsReport;

class PracticeInvoiceGenerator
{
    private $month;
    private $patients;

    private $practice;

    /**
     * PracticeInvoiceGenerator constructor.
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
     * @throws \Spatie\MediaLibrary\Exceptions\InvalidConversion
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     *
     * @return array
     */
    public function generatePdf($withItemized = true)
    {
        $invoiceName = trim($this->practice->name).'-'.$this->month->toDateString().'-invoice';

        $pdfInvoice = $this->makeInvoicePdf($invoiceName);

        $data = [
            'invoice_url' => $pdfInvoice->getUrl(),
            'mediaIds'    => [$pdfInvoice->id],
        ];

        if ($withItemized) {
            $reportName       = trim($this->practice->name).'-'.$this->month->toDateString().'-patients';
            $pdfPatientReport = $this->makePatientReportCsv($reportName);

            $data['patient_report_url'] = $pdfPatientReport->getUrl();
            $data['mediaIds'][]         = $pdfPatientReport->id;
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
        $pdf  = $pdfService->createPdfFromView(
            'billing.practice.invoice',
            $this->practice->getInvoiceData($this->month),
            $path
        );

        return $this->practice
            ->addMedia($path)
            ->toMediaCollection("invoice_for_{$this->month->toDateString()}");
    }

    /**
     * @param $reportName
     *
     * @return \Spatie\MediaLibrary\Models\Media
     */
    public function makePatientReportCsv($reportName)
    {
        return (new FromArray(
            "${reportName}.csv",
            (new ItemizedBillablePatientsReport(
                $this->practice->id,
                $this->practice->display_name,
                $this->month
            ))->toArrayForCsv(),
            [
            ]
        ))->storeAndAttachMediaTo($this->practice, "patient_report_for_{$this->month->toDateString()}");
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

        $pdf = $pdfService->createPdfFromView(
            'billing.practice.itemized',
            (new ItemizedBillablePatientsReport(
                $this->practice->id,
                $this->practice->display_name,
                $this->month
            ))->toArray(),
            $path
        );

        return $this->practice
            ->addMedia($path)
            ->toMediaCollection("patient_report_for_{$this->month->toDateString()}");
    }
}
