<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Rules;

use CircleLinkHealth\SelfEnrollment\Entities\EnrollmentInvitationLetterV2;
use Illuminate\Contracts\Validation\Rule;

class CanSaveNewSelfEnrollmentDiyLetter implements Rule
{
    private ?int $letterId;
    private ?int $practiceId;

    /**
     * Create a new rule instance.
     */
    public function __construct(?int $practiceId, ?int $letterId)
    {
        $this->practiceId = $practiceId;
        $this->letterId   = $letterId;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if ( ! $this->practiceId) {
            return 'Please save letter and then come back to update this field.';
        }

        return 'An active letter already exists for this practice.';
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $activeIsChecked = boolval($value);

        if ( ! $this->practiceId && $activeIsChecked) {
            return false;
        }

        if ($this->activeLetterForPracticeExists() && $activeIsChecked) {
            return false;
        }

        return true;
    }

    private function activeLetterForPracticeExists()
    {
        return EnrollmentInvitationLetterV2::whereNotIn('id', [$this->letterId])
            ->where('practice_id', $this->practiceId)
            ->where('is_active', true)
            ->exists();
    }
}
