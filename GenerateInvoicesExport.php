<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Modules\Nurseinvoices;

use App\Exports\InvoicesExportFormat;
use CircleLinkHealth\Core\Exports\FromArray;
use CircleLinkHealth\Core\Services\PdfService;
use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\Exceptions\InvalidConversion;

class GenerateInvoicesExport
{
    /**
     * @var
     */
    private $date;
    private $downloadFormat;

    /**
     * @var Collection
     */
    private $invoicesPerPractice;

    /**
     * GenerateInvoicesExport constructor.
     */
    public function __construct(Collection $invoicesPerPractice, string $downloadFormat, \Carbon\Carbon $date)
    {
        $this->invoicesPerPractice = $invoicesPerPractice;
        $this->date                = $date;
        $this->downloadFormat      = $downloadFormat;
    }

    public function generateInvoiceCsv()
    {
        $month  = Carbon::parse($this->date)->toDateString();
        $medias = [];
        foreach ($this->invoicesPerPractice as $practiceId => $invoices) {
            $practice     = Practice::findOrFail($practiceId);
            $downloadName = trim($practice->display_name).'-'.$month;
            $medias[]     = (new FromArray(
                "$downloadName.csv",
                (new InvoicesExportFormat(
                    $invoices
                ))->toCsvArray(),
                InvoicesExportFormat::headings()
            ))->storeAndAttachMediaTo($practice, $downloadName);
        }

        return $medias;
    }

    /**
     * @return array
     */
    public function generateInvoicePdf()
    {
        $data = [];
//        Export invoices to pdf grouped by practice.
        foreach ($this->invoicesPerPractice as $practiceId => $invoices) {
            $practice         = Practice::find($practiceId);
            $invoicesForMonth = Carbon::parse($this->date)->toDateString();
            $downloadName     = trim("$invoicesForMonth").'-pdf'.'-'.$practice->display_name;
            $pdfInvoices      = $this->makeInvoicesPdf($downloadName, $practice, $invoices);

            $data[] = [
                'invoice_url' => $pdfInvoices->getUrl(),
                'mediaIds'    => [$pdfInvoices->id],
            ];
        }

        return $data;
    }

    /**
     * @throws FileCannotBeAdded
     * @throws FileCannotBeAdded\DiskDoesNotExist
     * @throws FileCannotBeAdded\FileDoesNotExist
     * @throws FileCannotBeAdded\FileIsTooBig
     * @throws InvalidConversion
     *
     * @return \Spatie\MediaLibrary\Models\Media
     */
    private function makeInvoicesPdf(string $downloadName, Practice $practice, Collection $invoices)
    {
        $pdfService = app(PdfService::class);
        $path       = storage_path("download/$downloadName.pdf");

        $pdfInvoices = (new InvoicesExportFormat($invoices))->exportToPdf($pdfService);
        $pdfService->mergeFiles($pdfInvoices, $path);

        return $practice
            ->addMedia($path)
            ->toMediaCollection("pdf_invoices_for_{$practice->display_name}{$this->date->toDateString()}");
    }
}
