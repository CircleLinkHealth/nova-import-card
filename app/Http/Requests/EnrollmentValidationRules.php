<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Requests;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class EnrollmentValidationRules extends FormRequest
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
     * @param $url
     *
     * @return mixed
     */
    public function parseUrl($url)
    {
        $parsedUrl = parse_url($url);
        parse_str($parsedUrl['query'], $output);

        return $output['signature'];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'url_with_token' => 'required|string',
            'birth_date'     => 'required|date',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->inputIsInvalid($validator->getData())) {
                $validator->errors()->add('field', 'Your credentials do not match our records');
            }
        });
    }

    private function inputIsInvalid($input)
    {
        $user = User::with('patientInfo')->where('id', $input['user_id'])->firstOrFail();
        if ($input['is_survey_only']) {
            $enrollee = Enrollee::where('user_id', $input['user_id'])->firstOrFail();
            /** @var Enrollee $enrollee */
            $dbToken = $enrollee->getLastEnrollmentInvitationLink()->link_token;
        } else {
            /** @var User $user */
            $dbToken = $user->getLastEnrollmentInvitationLink()->link_token;
        }

        $inputToken = $this->parseUrl($input['url_with_token']);
        if ($dbToken !== $inputToken
            || Carbon::parse($input['birth_date'])->startOfDay()->ne($user->patientInfo->birth_date)) {
            return true;
        }

        return false;
    }
}
