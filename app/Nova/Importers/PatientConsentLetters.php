<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers;

use App\Constants;
use App\Notifications\NotifyDownloadMediaCollection;
use App\Notifications\SendSignedUrlToDownloadPatientProblemsReport;
use App\Services\PdfService;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterImport;
use URL;
use ZipArchive;

class PatientConsentLetters implements WithChunkReading, ToModel, WithHeadingRow, ShouldQueue, WithEvents
{
    use Importable;
    use RegistersEventListeners;

    protected $attributes;

    protected $collectionName;

    protected $date;

    protected $email;

    protected $fields;

    protected $fileName;

    protected $modelClass;

    /**
     * @var PdfService
     */
    protected $pdfService;

    protected $repo;

    protected $resource;

    protected $rules;

    public function __construct($resource, $attributes, $rules, $modelClass)
    {
        $this->resource       = $resource;
        $this->attributes     = $attributes;
        $this->rules          = $rules;
        $this->modelClass     = $modelClass;
        $this->fields         = $resource->fields;
        $this->email          = $resource->fields['Email'];
        $this->fileName       = $resource->fileName;
        $this->pdfService     = app(PdfService::class);
        $this->date           = Carbon::now();
        $this->collectionName = "patient-consent-letters-{$this->fileName}-{$this->date->toDateString()}";
    }

    public static function afterImport(AfterImport $event)
    {
        User::whereEmail($event->getConcernable()->email)->first()->notify(new NotifyDownloadMediaCollection($event->getConcernable()->collectionName));
    }

    public function batchSize(): int
    {
        return 10;
    }

    public static function beforeImport(AfterImport $event)
    {
        User::whereEmail($event->getConcernable()->email)->first()->clearMediaCollection($event->getConcernable()->collectionName);
    }

    public function chunkSize(): int
    {
        return 10;
    }

    public function message(): string
    {
        return 'File queued for importing. Patient Consent Letters will be created.';
    }

    public function model(array $row)
    {
        $fileName = $row['name'].'-consent-letter-'.Carbon::today()->toDateString().'pdf';
        $filePath = storage_path('pdfs/'.$fileName);
        $pdf      = app('snappy.pdf.wrapper');
        $pdf->loadView(
            'pdfs.patient-consent-letter',
            [
                'patientName' => $row['name'],
                'dob'         => $row['dob'],
                'mrn'         => $row['mrn'],
                'date'        => $row['consent_date'],
            ]
        );
        $pdf->save($filePath, true);

        User::whereEmail($this->email)->first()->addMedia($filePath)->toMediaCollection($this->collectionName);
    }

    public function rules(): array
    {
        return $this->rules;
    }
}
