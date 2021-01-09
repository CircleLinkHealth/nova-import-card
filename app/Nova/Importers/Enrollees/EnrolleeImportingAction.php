<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers\Enrollees;

use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

abstract class EnrolleeImportingAction implements WithChunkReading, OnEachRow, WithHeadingRow, ShouldQueue, WithEvents
{
    use Importable;
    use RegistersEventListeners;

    protected $attributes;

    protected ?int $caId;

    protected int $chunkSize = 100;

    protected string $fileName;

    protected $modelClass;

    protected int $practiceId;

    protected $resource;

    protected int $rowNumber = 2;

    protected $rules;

    public function __construct(int $practiceId, string $fileName, ?int $caId)
    {
        ini_set('upload_max_filesize', '200M');
        ini_set('post_max_size', '200M');
        ini_set('max_input_time', 900);
        ini_set('max_execution_time', 900);

        $this->practiceId = $practiceId;
        $this->fileName   = $fileName;
        $this->caId       = $caId;
    }

    public function chunkSize(): int
    {
        return $this->chunkSize;
    }

    public function message(): string
    {
        return 'File queued for importing.';
    }

    public function onRow(Row $row)
    {
        $this->execute($row->toArray());
        $this->incrementRowNumber();
    }

    public function rules(): array
    {
        return $this->rules;
    }

    protected function execute(array $row): void
    {
        if ( ! $this->validateRow($row)) {
            Log::channel('database')->warning("Input Validation for CSV:{$this->fileName}, at row: {$this->rowNumber}, failed.");

            return;
        }

        if (is_null($enrollee = $this->fetchEnrollee($row))) {
            Log::channel('database')->warning("Patient not found for CSV:{$this->fileName}, for row: {$this->rowNumber}.");

            return;
        }

        $actionInput = $this->getActionInput($enrollee, $row);

        if ( ! $this->shouldPerformAction($enrollee, $actionInput)) {
            Log::channel('database')->warning("Action not performed for Patient for CSV:{$this->fileName}, for row: {$this->rowNumber}. Please investigate");

            return;
        }

        $this->performAction($enrollee, $actionInput);
    }

    abstract protected function fetchEnrollee(array $row): ?Enrollee;

    abstract protected function getActionInput(Enrollee $enrollee, array $row): array;

    protected function incrementRowNumber(): void
    {
        ++$this->rowNumber;
    }

    abstract protected function performAction(Enrollee $enrollee, array $actionInput): void;

    abstract protected function shouldPerformAction(Enrollee $enrollee, array $actionInput): bool;

    abstract protected function validateRow(array $row): bool;
}
