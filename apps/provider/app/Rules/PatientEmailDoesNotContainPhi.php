<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Rules;

use App\Constants;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PatientEmailDoesNotContainPhi implements Rule
{
    private $field;
    private $patientUser;

    private $phiFound = [];

    private $transformable = [
        //attribute key/field
        'gender' => [
            //value -> transform to
            'm' => 'male',
            'f' => 'female',
        ],
    ];

    /**
     * Create a new rule instance.
     *
     * @param string $field
     */
    public function __construct(User $patientUser)
    {
        $this->patientUser            = $patientUser;
        $this->transformable['state'] = array_map('strtolower', array_change_key_case(usStatesArrayForDropdown(), CASE_LOWER));
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        $phiFieldsString = collect($this->phiFound)->transform(function ($field) {
            return Str::title(str_replace('_', ' ', $field));
        })->implode(', ');

        return "Email {$this->field} contains patient PHI: ".$phiFieldsString;
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
        $this->field = 'patient_email_body' == $attribute ? 'body' : 'subject';

        //check if string contains and just add fields that are found. Looping over each one individually, so we can report back to the user which phi exist in the message
        $value = strtolower($value);

        //For User
        foreach ($this->patientUser->phi as $phi) {
            $string = $this->getSanitizedAndTransformedAttribute($this->patientUser, $phi);

            if ($string) {
                $this->phiFound[] = $this->stringsMatch($string, $value)
                    ? $phi
                    : null;
            }
        }

        //For Relationships
        $this->patientUser->loadMissing(Constants::PATIENT_PHI_RELATIONSHIPS);
        foreach (Constants::PATIENT_PHI_RELATIONSHIPS as $relation) {
            foreach ($this->patientUser->{$relation}->phi as $phi) {
                $string = $this->getSanitizedAndTransformedAttribute($this->patientUser->{$relation}, $phi);
                if ($string) {
                    $this->phiFound[] = $this->stringsMatch($string, $value)
                        ? $phi
                        : null;
                }
            }
        }

        $this->phiFound = array_filter($this->phiFound);

        return empty($this->phiFound);
    }

    private function getSanitizedAndTransformedAttribute(Model $model, $phi)
    {
        $string = trim(strtolower($model->getAttribute($phi)));

        if (array_key_exists($phi, $this->transformable)) {
            if ( ! empty($string) && isset($this->transformable[$phi][$string])) {
                $string = $this->transformable[$phi][$string];
            }
        }

        return $string;
    }

    private function stringsMatch(string $string1, string $string2)
    {
        return preg_match("/\b".preg_quote($string1, '/')."\b/", $string2);
    }
}
