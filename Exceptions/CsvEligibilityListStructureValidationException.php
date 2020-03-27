<?php


namespace CircleLinkHealth\Eligibility\Exceptions;


use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;

class CsvEligibilityListStructureValidationException extends \Exception
{
    /**
     * @var array
     */
    protected $errors;
    /**
     * @var EligibilityBatch
     */
    protected $batch;
    
    /**
     * CsvEligibilityListStructureValidationException constructor.
     *
     * @param EligibilityBatch $batch
     * @param array $errors
     */
    public function __construct(EligibilityBatch $batch, array $errors)
    {
        $message = "Batch $batch->id (CSV) has invalid structure.";
        
        parent::__construct($message, 422, $previous = null);
        
        $this->errors = $errors;
        $this->batch = $batch;
    }
    
    /**
     * @return EligibilityBatch
     */
    public function getBatch(): EligibilityBatch
    {
        return $this->batch;
    }
    
    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}