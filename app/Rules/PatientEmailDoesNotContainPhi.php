<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Rules;

use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PatientEmailDoesNotContainPhi implements Rule
{
    const ALLOWED_PHI_FIELDS_KEY = 'allowed_phi_fields_in_patient_emails';
    const DEFAULT_ALLOWED_FIELDS = 'city,state,zip';

    private array $allowedFields;

    private $field;
    private $patientUser;

    private $phiFound = [];

    private $transformable = [
        'gender' => [
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
        $this->setFieldForValidationMessage($attribute);

        $value = strtolower($value);

        $this->validateAgainstPatientUserModel($value);
        $this->validateAgainstPatientRelationships($value);

        return ! $this->phiIsFound();
    }

    private function fieldIsName(string $field): bool
    {
        return in_array($field, [
            'first_name',
            'last_name',
        ]);
    }

    private function getSanitizedAndTransformedAttribute(Model $model, $phi)
    {
        $string = trim(strtolower($model->getAttribute($phi)));

        if (empty($string)) {
            return $string;
        }

        if ( ! isset($this->transformable[$phi])) {
            return $string;
        }

        if (is_array($this->transformable[$phi])) {
            return $this->transformable[$phi][$string] ?? $string;
        }

        return $string;
    }

    private function phiIsFound(): bool
    {
        $this->phiFound = array_filter($this->phiFound);

        return ! empty($this->phiFound);
    }

    private function setFieldForValidationMessage(string $attribute): void
    {
        $this->field = 'patient_email_body' == $attribute ? 'body' : 'subject';
    }

    private function shouldAllowPhiInEmail(string $phiField): bool
    {
        if ( ! isset($this->allowedFields)) {
            $this->allowedFields = explode(',', AppConfig::pull(self::ALLOWED_PHI_FIELDS_KEY, self::DEFAULT_ALLOWED_FIELDS));
        }

        return in_array($phiField, $this->allowedFields);
    }

    private function stringsMatch(string $string1, string $string2)
    {
        return preg_match("/\b".preg_quote($string1, '/')."\b/", $string2);
    }

    private function validateAgainstPatientRelationships(string $text)
    {
        $this->patientUser->loadMissing(CpmConstants::PATIENT_PHI_RELATIONSHIPS);
        foreach (CpmConstants::PATIENT_PHI_RELATIONSHIPS as $relation) {
            foreach ($this->patientUser->{$relation}->phi as $phi) {
                if ($this->shouldAllowPhiInEmail($phi)) {
                    continue;
                }
                $string = $this->getSanitizedAndTransformedAttribute($this->patientUser->{$relation}, $phi);
                if ($string) {
                    $this->phiFound[] = $this->stringsMatch($string, $text)
                        ? $phi
                        : null;
                }
            }
        }
    }

    private function validateAgainstPatientUserModel(string $text)
    {
        foreach ($this->patientUser->phi as $phi) {
            if ($this->shouldAllowPhiInEmail($phi)) {
                continue;
            }
            $string = $this->getSanitizedAndTransformedAttribute($this->patientUser, $phi);

            if ($string) {
                $stringMatches = $this->stringsMatch($string, $text);

                if ($stringMatches && $this->fieldIsName($phi) && $this->stringsMatch('nurse'.' '.$string, $text)) {
                    continue;
                }
                $this->phiFound[] = $stringMatches
                    ? $phi
                    : null;
            }
        }
    }
}
