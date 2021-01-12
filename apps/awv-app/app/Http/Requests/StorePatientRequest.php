<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StorePatientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //user should already be authenticated to reach here
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
            'patient.firstName'          => 'required|string',
            'patient.lastName'           => 'required|string',
            'patient.dob'                => 'required|date',
            'patient.phoneNumber'        => 'required|string',
            'patient.email'              => 'sometimes|unique:users,email',
            'patient.appointment'        => 'sometimes|date',
            'provider.id'                => 'nullable',
            'provider.firstName'         => 'nullable|string',
            'provider.lastName'          => 'nullable|string',
            'provider.suffix'            => 'nullable|string',
            'provider.primaryPracticeId' => 'required',
            'provider.specialty'         => 'nullable|string',
            'provider.isClinical'        => 'nullable|boolean',
            'provider.emrDirect'         => 'nullable|string',
        ];
    }
}
