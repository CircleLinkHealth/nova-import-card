<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Requests;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class MarkPrimaryPhone extends FormRequest
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
            'patientUserId' => 'required',
            'phoneId'       => 'required',
        ];
    }

    /**
     * @param $validator
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $input = $validator->getData();
            $userId = $input['patientUserId'];

            /** @var User $patientUser */
            $patientUser = User::with('patientInfo.location', 'phoneNumbers', 'primaryPractice')
                ->where('id', $userId)
                ->first();

            if (empty($patientUser)) {
                Log::error("User [$userId] not found");
                $validator->errors()->add('patientUserId', "User [$userId] not found");
            }

            $this->request->add([
                'patientUser' => $patientUser,
            ]);
        });
    }
}
