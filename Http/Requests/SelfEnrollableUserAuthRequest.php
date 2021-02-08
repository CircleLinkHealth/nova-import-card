<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SelfEnrollment\Http\Requests;

use CircleLinkHealth\SelfEnrollment\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SelfEnrollableUserAuthRequest extends FormRequest
{
    const DATE_LAST_DUPLICATE_SHORT_LINKS_EXPIRE = '2020-06-05';
    /**
     * @var mixed
     */
    private $user;

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
                    $user = User::find($value);
                    if ( ! $user->exists()) {
                        $fail('Invalid User.');
                    }
                    $this->user = $user;
                },
            ],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function (Validator $validator) {
            $input = $validator->getData();
            $dob = Carbon::create((int) $input['birth_date_year'], (int) $input['birth_date_month'], (int) $input['birth_date_day']);
            $enrolleeQuery = $this->enrolleeQuery($dob, $userId = (int) $input['user_id'], $isSurveyOnly = (bool) $input['is_survey_only']);
            $helpLoginMessage = $this->helpLoginMessage();

            if ($validator->failed()) {
                $validator->errors()->add('help_login', $helpLoginMessage);
                return;
            }

            if ($enrolleeQuery->exists()) {
                $this->request->add([
                    'userId'=>$userId
                ]);
                return;
            }

            Log::channel('database')->error('Failed to login patient with DOB['.$dob->toDateString()."] and UserId[$userId] and isSurveyOnly[$isSurveyOnly]");
            $validator->errors()->add('field', 'Your credentials do not match our records');
            $validator->errors()->add('help_login', $helpLoginMessage);

            $this->request->add([
                'userId'=>$userId
            ]);
        });
    }

    private function enrolleeQuery(Carbon $dob, int $userId, bool $IsSurveyOnly): \Illuminate\Database\Eloquent\Builder
    {
        return User::where('id', $userId)
            ->whereHas('patientInfo', function ($q) use ($dob) {
                $q->where('birth_date', $dob);
            })->whereHas('enrollee', function ($q) {
                $q->whereIn('status', [
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

    private function helpLoginMessage()
    {
        $practiceNumber =  $this->user->primaryProgramPhoneE164();

        if ($practiceNumber){
            return "If you are having trouble to log in, please contact your practice at $practiceNumber.";
        }

        return "If you are having trouble to log in, please contact your practice.";

    }

}
