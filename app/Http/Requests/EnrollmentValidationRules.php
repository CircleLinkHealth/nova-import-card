<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Requests;

use App\SelfEnrollment\Helpers;
use Carbon\Exceptions\InvalidFormatException;
use CircleLinkHealth\Customer\EnrollableInvitationLink\EnrollableInvitationLink;
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
            if ($validator->failed()) {
                return;
            }

            if ($this->inputIsInvalid($validator->getData())) {
                $validator->errors()->add('field', 'Your credentials do not match our records');
            }
        });
    }

    private function hasValidUrl(EnrollableInvitationLink $link, string $currentUrl)
    {
        if (empty($link)) {
            return false;
        }

        return $link->link_token === Helpers::getTokenFromUrl($currentUrl);
    }

    private function inputIsInvalid($input)
    {
        $previousUrl = url()->previous();

        $potentialEnrolleeCount = Enrollee::where('dob', \Carbon\Carbon::create($input['birth_date_year'], $input['birth_date_month'], $input['birth_date_day']))
            ->where('status', Enrollee::QUEUE_AUTO_ENROLLMENT)
            ->leftJoin('enrollables_invitation_links', function ($join) {
                $join->on('enrollables_invitation_links.invitationable_id', '=', 'enrollees.id')
                    ->where('invitationable_type', Enrollee::class);
            })
            ->leftJoin('short_urls', function ($join) use ($previousUrl) {
                $join->on('enrollables_invitation_links.url', '=', 'short_urls.destination_url')
                    ->where('destination_url', $previousUrl);
            })->with('user.patientInfo')->count();

        if ($potentialEnrolleeCount) {
            $user = $enrollable = User::with('patientInfo')->where('id', $input['user_id'])->firstOrFail();
        }
        if ($input['is_survey_only']) {
            $enrollable = Enrollee::where('user_id', $input['user_id'])->firstOrFail();
        }

        $links = $enrollable->enrollmentInvitationLinks()->join();

        if ( ! $this->hasValidUrl($link, $input['url_with_token'])) {
            Log::warning("EnrollmentValidationRules: User id [$user->id] has corrupted url token");

            return true;
        }

        $day   = intval($input['birth_date_day']);
        $month = intval($input['birth_date_month']);
        $year  = intval($input['birth_date_year']);
        if (0 === $day || 0 === $month || 0 === $year) {
            Log::warning("EnrollmentValidationRules: User id [$user->id] has entered wrong date format");

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
            Log::critical("EnrollmentValidationRules: User id [$user->id] has entered wrong DOB");

            return true;
        }

        return false;
    }
}
