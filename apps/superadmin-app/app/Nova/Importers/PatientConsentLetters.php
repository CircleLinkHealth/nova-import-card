<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers;

use App\Notifications\NotifyDownloadMediaCollection;
use Carbon\Carbon;
use CircleLinkHealth\Core\Services\PdfService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\BeforeImport;

class PatientConsentLetters implements WithChunkReading, ToModel, WithHeadingRow, ShouldQueue, WithEvents
{
    use Importable;
    use RegistersEventListeners;

    protected $attributes;

    protected $collectionName;

    protected $date;

    protected $fileName;

    protected $modelClass;

    /**
     * @var PdfService
     */
    protected $pdfService;

    protected $repo;

    protected $resource;

    protected $rules;

    protected $user;

    public function __construct($resource, $attributes, $rules, $modelClass)
    {
        $this->resource       = $resource;
        $this->attributes     = $attributes;
        $this->rules          = $rules;
        $this->modelClass     = $modelClass;
        $this->user           = $resource->fields->getFieldValue('Email');
        $this->fileName       = $resource->fileName;
        $this->pdfService     = app(PdfService::class);
        $this->date           = Carbon::now();
        $this->collectionName = "patient-consent-letters-{$this->fileName}-{$this->date->toDateString()}";
    }

    public static function afterImport(AfterImport $event)
    {
        $event->getConcernable()->user->notify(new NotifyDownloadMediaCollection($event->getConcernable()->collectionName));
    }

    public function batchSize(): int
    {
        return 10;
    }

    public static function beforeImport(BeforeImport $event)
    {
        $event->getConcernable()->user->clearMediaCollection($event->getConcernable()->collectionName);
    }

    public function chunkSize(): int
    {
        return 10;
    }

    public function message(): string
    {
        return 'File queued for importing. Patient Consent Letters will be created and and email containing download link will be sent.';
    }

    public function model(array $row)
    {
        $fileName = $row['name'].'-consent-letter-'.Carbon::today()->toDateString().'.pdf';
        $filePath = storage_path('pdfs/'.$fileName);
        $pdf      = app('snappy.pdf.wrapper');
        $pdf->loadView(
            'pdfs.patient-consent-letter',
            [
                'patientName' => $row['name'],
                'dob'         => $this->getDate($row['dob']),
                'mrn'         => $row['mrn'],
                'date'        => $this->getDate($row['consent_date']),
            ]
        );
        $pdf->save($filePath, true);

        $this->user->addMedia($filePath)->toMediaCollection($this->collectionName);
    }

    public function rules(): array
    {
        return $this->rules;
    }

    private function getDate($field)
    {
        try {
            $date = Carbon::parse($field);
        } catch (\Exception $exception) {
            try {
                $date = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($field));
            } catch (\Exception $exc) {
                return null;
            }
        }

        return $date->toDateString();
    }
}
