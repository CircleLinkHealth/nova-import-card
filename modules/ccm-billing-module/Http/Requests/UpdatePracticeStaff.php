<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePracticeStaff extends FormRequest
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
        $emailRules = 'required|email';
        $userId     = $this->get('id', null);

        //CPM-754: if the user is new, make sure we do not have existing user with this email
        if ($userId && 'new' === $userId) {
            $emailRules .= '|unique:users';
        }

        return [
            'practice_id'        => 'required|exists:practices,id',
            'email'              => $emailRules,
            'first_name'         => 'required',
            'last_name'          => 'required',
            'suffix'             => 'sometimes',
            'phone_number'       => 'nullable|phone:US',
            'phone_type'         => 'required_with:phone_number',
            'phone_extension'    => 'nullable',
            'emr_direct_address' => 'nullable|email',
            'role_names'         => 'required',
            'ehr_id'             => 'nullable|exists:ehrs,id|required_with:ehr_username',
            'ehr_username'       => 'required_with:ehr_id',
        ];
    }
}
