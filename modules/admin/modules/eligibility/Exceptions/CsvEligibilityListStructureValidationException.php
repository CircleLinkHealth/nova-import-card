<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Exceptions;

use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;

class CsvEligibilityListStructureValidationException extends \Exception
{
    /**
     * @var EligibilityBatch
     */
    protected $batch;
    /**
     * @var array
     */
    protected $errors;

    /**
     * CsvEligibilityListStructureValidationException constructor.
     */
    public function __construct(EligibilityBatch $batch, array $errors)
    {
        $message = "Batch $batch->id (CSV) has invalid structure.";

        parent::__construct($message, 422, $previous = null);

        $this->errors = $errors;
        $this->batch  = $batch;
    }

    public function getBatch(): EligibilityBatch
    {
        return $this->batch;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
