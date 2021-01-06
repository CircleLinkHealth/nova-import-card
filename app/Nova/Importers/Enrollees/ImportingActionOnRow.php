<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers\Enrollees;


use CircleLinkHealth\SharedModels\Entities\Enrollee;

abstract class ImportingActionOnRow
{
    protected array $row;
    protected string $fileName;
    protected int $rowNumber;

    public function __construct(array $row, string $fileName, int $rowNumber)
    {
        $this->row = $row;
        $this->fileName = $fileName;
        $this->rowNumber = $rowNumber;
    }

    protected abstract function fetchEnrollee();

    protected abstract function validate(Enrollee $enrollee);

    protected abstract function performAction(Enrollee $enrollee);

    public function execute(): void
    {
        if (is_null($enrollee =$this->fetchEnrollee())){
            return;
        }

        if (! $this->validate($enrollee)){
            return;
        }

        $this->performAction($enrollee);
    }
}