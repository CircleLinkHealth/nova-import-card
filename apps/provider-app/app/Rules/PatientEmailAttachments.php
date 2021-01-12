<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class PatientEmailAttachments implements Rule
{
    protected $errorMessage;

    /**
     * Create a new rule instance.
     */
    public function __construct()
    {
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->errorMessage;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if ( ! is_array($value)) {
            $this->errorMessage = 'Something went wrong with the attached files: "Attachments is not an array".';

            return false;
        }

        foreach ($value as $attachment) {
            if ( ! array_keys_exist(['media_id', 'path'], $attachment)) {
                $this->errorMessage = 'Something went wrong with the attached files: "Attachments array keys missing".';

                return false;
            }
            if (empty(trim($attachment['media_id']))) {
                $this->errorMessage = 'Something went wrong with the attached files: "Media Id is empty".';

                return false;
            }
            if (empty(trim($attachment['path']))) {
                $this->errorMessage = 'Something went wrong with the attached files: "Path is empty".';

                return false;
            }
        }

        return true;
    }
}
