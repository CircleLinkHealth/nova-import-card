<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Requests;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SelfEnrollableUserAuthRequest extends FormRequest
{
    const DATE_LAST_DUPLICATE_SHORT_LINKS_EXPIRE = '2020-06-05';

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
            'is_survey_only' => [
                'required',
                Rule::in(['0', '1']),
            ],
            'birth_date_day'   => 'required|numeric|min:1|max:31',
            'birth_date_month' => 'required|numeric|min:1|max:12',
            'birth_date_year'  => 'required|numeric|min:1900|max:'.now()->subYears(18)->year,
            'user_id'          => [
                'required',
                function ($attribute, $value, $fail) {
                    //once we phase out problematic links batch, also check that user has survey only, or patient role
                    $exists = User::where('id', $value)->exists();
                    if ( ! $exists) {
                        $fail('Invalid User.');
                    }
                },
            ],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function (Validator $validator) {
            if ($validator->failed()) {
                return;
            }

            $input = $validator->getData();
            $dob = Carbon::create((int) $input['birth_date_year'], (int) $input['birth_date_month'], (int) $input['birth_date_day']);

            if ($this->enrolleeQuery($dob, $userId = (int) $input['user_id'], $isSurveyOnly = (bool) $input['is_survey_only'])->exists()) {
                return;
            }

            \Log::channel('database')->error('Failed to login patient with DOB['.$dob->toDateString()."] and UserId[$userId] and isSurveyOnly[$isSurveyOnly]");

            $validator->errors()->add('field', 'Your credentials do not match our records');
        });
    }

    private function enrolleeQuery(Carbon $dob, int $userId, bool $IsSurveyOnly): \Illuminate\Database\Eloquent\Builder
    {
        return User::where('id', $userId)
            ->whereHas('patientInfo', function ($q) use ($dob) {
                $q->where('birth_date', $dob);
            })->whereHas('enrollee', function ($q) use ($dob) {
                $q->where('dob', $dob)
                    ->whereIn('status', [
                        Enrollee::QUEUE_AUTO_ENROLLMENT,
                        Enrollee::TO_CALL,
                        Enrollee::SOFT_REJECTED,
                    ])
                    ->where(function ($q) {
                        $q->where('source', '=', Enrollee::UNREACHABLE_PATIENT)
                            ->orWhereNull('source');
                    });
            })
            ->ofType($IsSurveyOnly ? 'survey-only' : 'participant');
    }
}
