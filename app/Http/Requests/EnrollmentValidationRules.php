<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Requests;

use App\SelfEnrollment\Helpers;
use Carbon\Exceptions\InvalidFormatException;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
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

        $inputToken = Helpers::getTokenFromUrl($input['url_with_token']);
        if (empty($link) || $link->link_token !== $inputToken) {
            return true;
        }

        $day   = intval($input['birth_date_day']);
        $month = intval($input['birth_date_month']);
        $year  = intval($input['birth_date_year']);
        if (0 === $day || 0 === $month || 0 === $year) {
            return true;
        }

        try {
            $inputDobStr = "$year-$month-$day";
            $inputDob    = Carbon::parse($inputDobStr);
        } catch (InvalidFormatException $e) {
            Log::warning("EnrollmentValidationRules: {$e->getMessage()}");

            return true;
        }

        $actualDob = optional($user->patientInfo->birth_date)->startOfDay();
        if ($inputDob->startOfDay()->ne($actualDob)) {
            return true;
        }

        return false;
    }
}
