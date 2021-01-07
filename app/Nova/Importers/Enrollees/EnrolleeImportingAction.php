<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers\Enrollees;


use App\Nova\Actions\ImportEnrollees;
use Carbon\Carbon;
use CircleLinkHealth\Core\StringManipulation;
use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Eligibility\CcdaImporter\CcdaImporterWrapper;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportPatientInfo;
use CircleLinkHealth\Eligibility\SelfEnrollment\Jobs\CreateSurveyOnlyUserFromEnrollee;
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

    protected $modelClass;

    protected $resource;

    protected int $rowNumber = 2;

    protected $rules;

    protected ?int $caId;

    protected string $fileName;

    protected int $chunkSize = 100;

    protected int $practiceId;

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

    protected function incrementRowNumber():void
    {
        $this->rowNumber++;
    }

    protected function execute(array $row): void
    {
        if (! $this->validateRow($row)){
            Log::channel('database')->warning("Input Validation for CSV:{$this->fileName}, at row: {$this->rowNumber}, failed.");
            return;
        }

        if (is_null($enrollee = $this->fetchEnrollee($row))){
            Log::channel('database')->warning("Patient not found for CSV:{$this->fileName}, for row: {$this->rowNumber}.");
            return;
        }

        $actionInput = $this->getActionInput($enrollee, $row);

        if (! $this->shouldPerformAction($enrollee, $actionInput)){
            Log::channel('database')->warning("Action not performed for Patient for CSV:{$this->fileName}, for row: {$this->rowNumber}. Please investigate");
            return;
        }

        $this->performAction($enrollee, $actionInput);
    }

    public function rules(): array
    {
        return $this->rules;
    }

    protected abstract function fetchEnrollee(array $row) :? Enrollee;

    protected abstract function getActionInput(Enrollee $enrollee, array $row) :array;

    protected abstract function shouldPerformAction(Enrollee $enrollee, array $row) : bool;

    protected abstract function performAction(Enrollee $enrollee, array $actionInput): void;

    protected abstract function validateRow(array $row): bool;

}