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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ContactDetailsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
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
            'agentRelationship' => 'sometimes|regex:/^[\pL\s\-]+$/u', // allow only text-letters with spaces
            'agentName'         => 'sometimes|regex:/^[\pL\s\-]+$/u',
            'agentEmail'        => 'sometimes|email',
            'phoneType'         => 'sometimes|required',
        ];
    }

    public static function validateUser(?User $patientUser, Validator $validator)
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

            $userLocation = $patientUser->patientInfo->location->id
                ?? $patientUser->primaryPractice->primary_location_id
                ?? null;

            if ( ! is_null($phoneType) && $patientUser->phoneNumbers()
                ->where('type', $phoneType)
                ->where('number', '!=', '')
                ->whereNotNull('number')
                ->exists()) {
                $validator->errors()->add('phoneNumber', "Phone type '$phoneType' already exists for patient");
            }

            if ( ! allowNonUsPhones() && ! ImportPhones::validatePhoneNumber($input['phoneNumber'])) {
                $validator->errors()->add('phoneNumber', 'Phone number is not a valid US number');
            }

            if (is_null($userLocation)) {
                Log::error("Location for patient with user id: {$userId} not found");
                $validator->errors()->add('patientUserId', 'User location is missing');
            }

            $this->request->add([
                'patientUser' => $patientUser,
                'locationId'  => $userLocation,
            ]);
        });
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors()->getMessages(), 422));
    }
}
