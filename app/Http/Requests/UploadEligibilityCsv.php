<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadEligibilityCsv extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'patient_list' => ['required', 'mimes:csv,txt',
                function ($attribute, $value, $fail) {
                    try {
                        $patients = parseCsvToArray($value);
                    } catch (\Exception $e) {
                        return $fail('This file is not in a CSV format.');
                    }
                    if (empty($patients)) {
                        return $fail('We could not read data from the file you uploaded. It appears to be empty or not invalid. Please upload a non-empty/valid CSV file.');
                    }

                    //check if each row parsed from file is the same length, in case the file is json.
                    $count = null;
                    foreach ($patients as $patient) {
                        $newCount = count($patient);
                        if (null == $count) {
                            $count = $newCount;
                        } elseif ($count !== $newCount) {
                            return $fail('This file is not in a CSV format.');
                        } else {
                            $count = $newCount;
                        }
                    }

                    $this->request->add(['patients' => $patients]);

                    return true;
                }, ],
            'practice_id' => 'required|numeric',
        ];
    }
}
