<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Requests;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportPhones;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class ContactDetailsValidator extends FormRequest
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
            'patientUserId'     => 'required',
            'phoneNumber'       => 'required|numeric',
            'agentRelationship' => 'sometimes|alpha',
            'agentName'         => 'sometimes|alpha',
            'agentEmail'        => 'sometimes|email',
            'phoneType'         => 'sometimes|required',
        ];
    }

    public static function validateUser(User $patientUser, Validator $validator)
    {
        if (empty($patientUser)) {
            Log::error("User [$patientUser->id] not found");
            $validator->errors()->add('patientUserId', "User [$patientUser->id] not found");
        }
    }

    /**
     * @param $validator
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $input = $validator->getData();
            $userId = $input['patientUserId'];
            $phoneType = isset($input['phoneType']) ? $input['phoneType'] : null;

            /** @var User $patientUser */
            $patientUser = User::with('patientInfo.location', 'phoneNumbers', 'primaryPractice')
                ->where('id', $userId)
                ->first();

            self::validateUser($patientUser, $validator);

            $userLocationExists = isset($patientUser->patientInfo->location->id);
            $primaryLocationExists = isset($patientUser->primaryPractice->primary_location_id);

            if ( ! is_null($phoneType) && $patientUser->phoneNumbers()->where('type', $phoneType)->exists()) {
                $validator->errors()->add('phoneNumber', "Phone type '$phoneType' already exists for patient");
            }

            if ( ! ImportPhones::validatePhoneNumber($input['phoneNumber'])) {
                $validator->errors()->add('phoneNumber', 'Phone number is not a valid US number');
            }

            if ( ! $userLocationExists && ! $primaryLocationExists) {
                Log::error("Location for patient with user id: {$userId} not found");
                $validator->errors()->add('patientUserId', 'User location is missing');
            }

            $this->request->add([
                'patientUser' => $patientUser,
                'locationId'  => $userLocationExists
                    ? $patientUser->patientInfo->location->id
                    : $patientUser->primaryPractice->primary_location_id,
            ]);
        });
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors()->first(), 422));
    }
}
