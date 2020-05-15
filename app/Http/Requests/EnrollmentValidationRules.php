<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Requests;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

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
            'url_with_token'   => 'required|string',
            'birth_date_day'   => 'required|numeric|min:1|max:31',
            'birth_date_month' => 'required|numeric|min:1|max:12',
            'birth_date_year'  => 'required|numeric|min:1900|max:2000',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function (Validator $validator) {
            if ( ! $validator->failed()) {
                if ($this->inputIsInvalid($validator->getData())) {
                    $validator->errors()->add('field', 'Your credentials do not match our records');
                }
            }
        });
    }

    private function inputIsInvalid($input)
    {
        $user = User::with('patientInfo')->where('id', $input['user_id'])->firstOrFail();
        if ($input['is_survey_only']) {
            $enrollee = Enrollee::where('user_id', $input['user_id'])->firstOrFail();
            /** @var Enrollee $enrollee */
            $link = $enrollee->getLastEnrollmentInvitationLink();
        } else {
            /** @var User $user */
            $link = $user->getLastEnrollmentInvitationLink();
        }

        $inputToken = $this->parseUrl($input['url_with_token']);
        if (empty($link) || $link->link_token !== $inputToken) {
            return true;
        }

        $day         = $input['birth_date_day'];
        $month       = $input['birth_date_month'];
        $year        = $input['birth_date_year'];
        $inputDobStr = "$year-$month-$day";
        $inputDob    = Carbon::parse($inputDobStr);
        $actualDob   = optional($user->patientInfo->birth_date)->startOfDay();
        if ($inputDob->startOfDay()->ne($actualDob)) {
            return true;
        }

        return false;
    }
}
