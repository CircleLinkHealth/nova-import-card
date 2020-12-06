<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use Illuminate\Support\Collection;
use Validator;

class ProcessSelfEnrolmentCsvData
{
    public Collection $errors;
    /**
     * @var mixed
     */
    private $providerName;
    /**
     * @var mixed
     */
    private $userId;

    public function getErrors(): Collection
    {
        return $this->errors;
    }

    /**
     * @return Collection
     */
    public function processCsvCollection(Collection $dataFromCsv)
    {
        return $dataFromCsv->mapToGroups(function ($row) {
            $this->userId = $row[0];
            $this->providerName = $row[1];
            Validator::make($row->toArray(), $this->rules(), $this->validationMessages())->validate();

            return [
                $row[1] => $row[0],
            ];
        });
    }

    private function rules(): array
    {
        return [
            0 => 'required|numeric',
            1 => 'required|regex:/^[a-zA-Z.,\s]+$/',
        ];
    }

    private function validationMessages()
    {
        return [
            '0.numeric'  => "Eligible_patient_id should be numeric value. See [$this->userId]]",
            '0.required' => "Eligible_patient_id required. See [$this->providerName]]",
            '1.required' => "Provider Name is required. See [$this->userId]]",
            '1.regex'    => "Provider Name must be alphabetic. See [$this->providerName]]",
        ];
    }
}
