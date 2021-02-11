<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Rules;

use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Collection;
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

        $this->validateAgainstModel($this->patientUser, $value);
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

    private function isPreFixedWithTheWordNurse(string $string, $text): bool
    {
        return $this->stringsMatch('nurse'.' '.$string, $text);
    }

    private function patientNameMatchesNurseName($value): bool
    {
        $nurse = auth()->user();

        return strtolower($nurse->first_name) === $value || strtolower($nurse->last_name) === $value;
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

    private function shouldAllowIfItMatchesNurseNameAndIsPrefixedByTheWordNurse($field, $string, $text): bool
    {
        return $this->fieldIsName($field) && $this->patientNameMatchesNurseName($string) && $this->isPreFixedWithTheWordNurse($string, $text);
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

    private function validateAgainstCollection(Collection $collection, string $text)
    {
        foreach ($collection as $modelWithinCollection) {
            $this->validateAgainstModel($modelWithinCollection, $text);
        }
    }

    private function validateAgainstModel(Model $model, string $text)
    {
        foreach ($model->phi as $phi) {
            $this->validatePhiValue($this->patientUser, $phi, $text);
        }
    }

    private function validateAgainstPatientRelationships(string $text)
    {
        $this->patientUser->loadMissing(CpmConstants::PATIENT_PHI_RELATIONSHIPS);

        foreach (CpmConstants::PATIENT_PHI_RELATIONSHIPS as $relation) {
            $relation = $this->patientUser->{$relation};
            if (is_a($relation, Collection::class)) {
                $this->validateAgainstCollection($relation, $text);
                continue;
            }
            $this->validateAgainstModel($relation, $text);
        }
    }

    private function validatePhiValue($model, $field, $text): void
    {
        if ($this->shouldAllowPhiInEmail($field)) {
            return;
        }
        $string = $this->getSanitizedAndTransformedAttribute($model, $field);

        if (empty($string)) {
            return;
        }

        $stringMatches = $this->stringsMatch($string, $text);

        if ($stringMatches && $this->shouldAllowIfItMatchesNurseNameAndIsPrefixedByTheWordNurse($field, $string, $text)) {
            return;
        }

        $this->phiFound[] = $stringMatches
                ? $field
                : null;
    }
}
