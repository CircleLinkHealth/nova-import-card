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
            'url_with_token'   => 'required|string',
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

            $url = url()->previous();
            $input = $validator->getData();
            $dob = Carbon::create((int) $input['birth_date_year'], (int) $input['birth_date_month'], (int) $input['birth_date_day']);

            if ($this->enrolleeQuery($url, $dob, $input['user_id'], $input['is_survey_only'])->exists()) {
                return;
            }

            if ($this->shouldTryAlternative($url, $dob) && $user = $this->queryUserByDobAndUrl($url, $dob)->first()) {
                $this->replace([
                    'birth_date_day'   => $dob->day,
                    'birth_date_month' => $dob->month,
                    'birth_date_year'  => $dob->year,
                    'user_id'          => $user->id,
                ]);

                return;
            }

            $validator->errors()->add('field', 'Your credentials do not match our records');
        });
    }

    private function enrolleeQuery(string $url, Carbon $dob, int $userId, bool $IsSurveyOnly): \Illuminate\Database\Eloquent\Builder
    {
        return $this->queryUserByDobAndUrl($url, $dob)
            ->where('id', $userId)
            ->ofType($IsSurveyOnly ? 'survey-only' : 'participant');
    }

    private function queryUserByDobAndUrl(string $url, Carbon $dob)
    {
        return User::whereHas('patientInfo', function ($q) use ($dob) {
            $q->where('birth_date', $dob);
        })->whereHas('enrollee', function ($q) use ($dob, $url) {
            $q->where('dob', $dob)
                ->where('status', Enrollee::QUEUE_AUTO_ENROLLMENT)->where(function ($q) {
                    $q->where('source', '=', Enrollee::UNREACHABLE_PATIENT)
                        //Enrollee for self enrollment
                        ->orWhereNull('source');
                })
                ->leftJoin('enrollables_invitation_links', function ($join) {
                    $join->on('enrollables_invitation_links.invitationable_id', '=', 'enrollees.id')
                        ->where('invitationable_type', Enrollee::class);
                })
                ->leftJoin('short_urls', function ($join) use ($url) {
                    $join->on('enrollables_invitation_links.url', '=', 'short_urls.destination_url')
                        ->whereIn('url_key', function ($q) use ($url) {
                            $q->select('url_key')->from('enrollables_invitation_links')->where('destination_url', $url);
                        });
                });
        })->with('patientInfo');
    }

    /**
     * This is a temporary method to make up for a bug.
     *
     * On the week of May 23rd 2020, we sent some self enrollment invitations.
     * Some patients reported being unable to login after entering correct DOB.
     * We found Short Url package was generating non-unique URLs, and we already sent invitations out.
     * To compensate, if there exists an Enrollee with User that has the same short link and DOB provided by the current user, we will log them in.
     */
    private function shouldTryAlternative(string $url, Carbon $dob): bool
    {
        if (now()->gt(Carbon::create(self::DATE_LAST_DUPLICATE_SHORT_LINKS_EXPIRE))) {
            return false;
        }

        // To be defensive we only do this if there's only one match.
        return 1 === $this->queryUserByDobAndUrl($url, $dob)->count();
    }
}
