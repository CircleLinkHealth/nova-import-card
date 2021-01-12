<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Rules;

use Illuminate\Contracts\Validation\Rule;

class PasswordCharacters implements Rule
{
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
        return 'The password must be at least 8 characters, contain at least an uppercase letter, a number and a special character (!,$,#,%,@,&,*).';
    }

    /**
     * This regex will enforce these rules:
     * - At least one upper case English letter, (?=.*?[A-Z])
     * - At least one lower case English letter, (?=.*?[a-z])
     * - At least one digit, (?=.*?[0-9])
     * - At least one special character, (?=.*?[#?!@$%^&*-])
     * - Minimum eight in length .{8,} (with the anchors).
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return preg_match('/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/', $value);
    }
}
