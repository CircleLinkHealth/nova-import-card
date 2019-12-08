<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers;

use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterImport;

class PatientConsentLetters implements WithChunkReading, ToModel, WithHeadingRow, ShouldQueue, WithEvents
{
    use Importable;
    use RegistersEventListeners;

    protected $attributes;

    protected $fields;

    protected $fileName;

    protected $importingErrors = [];

    protected $modelClass;

    protected $repo;

    protected $resource;

    protected $rowNumber = 2;

    protected $rules;

    public function __construct($resource, $attributes, $rules, $modelClass)
    {
        $this->resource   = $resource;
        $this->attributes = $attributes;
        $this->rules      = $rules;
        $this->modelClass = $modelClass;
        $this->fields     = $resource->fields;
        $this->fileName   = $resource->fileName;
    }

    public static function afterImport(AfterImport $event)
    {
        $importer = $event->getConcernable();

        sendSlackMessage(
            '#background-tasks',
            "Queued job Import Practice Staff for practice {$importer->practice->display_name}, from file {$importer->fileName} is completed.\n"
        );
    }

    public function chunkSize(): int
    {
        return 200;
    }

    public function message(): string
    {
        return 'File queued for importing.';
    }

    public function model(array $row)
    {
        $x = 1;
    }

    public function rules(): array
    {
        return $this->rules;
    }
}
