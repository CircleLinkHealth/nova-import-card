<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Rules;

use CircleLinkHealth\CcmBilling\Domain\Patient\PatientProblemsForBillingProcessing;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientProblemForProcessing;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Collection;

class HasEnoughProblems implements Rule
{
    const VALIDATION_ERROR_TEXT = 'The Care Plan must have two CCM problems or one BHI, PCM or RPM problem.';
    /** @var User */
    private $patient;

    private Collection $problems;

    /**
     * Create a new rule instance.
     */
    public function __construct(User $patient)
    {
        $this->patient = $patient;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return self::VALIDATION_ERROR_TEXT;
    }

    /**
     * Determine if the validation rule passes.
     * $value is a collection of ccdProblems.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if ($value->isEmpty()) {
            return false;
        }

        $this->problems = PatientProblemsForBillingProcessing::getCollection($this->patient->id);

        foreach (ChargeableService::CODES_THAT_CAN_HAVE_PROBLEMS as $code) {
            if ($this->hasEnoughProblemsForCode($code)) {
                return true;
            }
        }

        return false;
    }

    private function hasEnoughProblemsForCode(string $code): bool
    {
        return $this->problems
            ->filter(
                fn (PatientProblemForProcessing $p) => 0 != count(array_intersect([$code], $p->getServiceCodes()))
            )
            ->count()
            >= (PatientProblemsForBillingProcessing::SERVICE_PROBLEMS_MIN_COUNT_MAP[$code] ?? 0);
    }
}
