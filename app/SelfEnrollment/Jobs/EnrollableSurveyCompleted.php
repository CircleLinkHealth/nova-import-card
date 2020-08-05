<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\SelfEnrollment\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\Jobs\ImportConsentedEnrollees;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnrollableSurveyCompleted implements ShouldQueue
{
    use Dispatchable;

    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    const SURVEY_COMPLETED = 'completed';
    private $data;

    /**
     * Create a new job instance.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function deleteBatch($userId)
    {
        EligibilityBatch::whereInitiatorId($userId)->forceDelete();
    }

    /**
     * @param $enrollableId
     * @param $surveyInstanceId
     * @param $identifier
     * @return Collection
     */
    public function getAnswerForQuestionUsingIdentifier($enrollableId, $surveyInstanceId, $identifier)
    {
        $surveyInstance     = DB::table('survey_instances')->where('id', '=', $surveyInstanceId)->first();
        $enrolleesQuestions = DB::table('questions')->where('survey_id', '=', $surveyInstance->survey_id)->get();

        $enrollableSurveyData = DB::table('answers')
            ->where('user_id', '=', $enrollableId)
            ->where('survey_instance_id', $surveyInstanceId)->get();

        return $this->getSanitizedAnswerValue($enrolleesQuestions, $enrollableSurveyData, $identifier);
    }

    /**
     * @param $val
     *
     * @return mixed
     */
    public function getArrayValue($val)
    {
        return $val[0];
    }

    public function getPreferredContactHoursToString($preferredTime)
    {
        if (is_object($preferredTime)) {
            $preferredTime = $preferredTime->toArray();
        }

        return createTimeRangeFromEarliestAndLatest($preferredTime);
    }

    /**
     * @param $days
     *
     * @return string
     */
    public function getPreferredDaysToString($days)
    {
        $dow = [];
        if (is_object($days)) {
            $days = $days->toArray();
        }

        if (is_array($days)) {
            foreach ($days as $day) {
                $dow[] = clhToCarbonDayOfWeek(Carbon::parse($day)->dayOfWeek);
            }
        } else {
            return Carbon::parse($days)->dayOfWeek;
        }

        return implode(', ', $dow);
    }

    /**
     * @param $times
     *
     * @return string
     */
    public function getPreferredTimesToString($times)
    {
        return $times[0]->from.'-'.$times[0]->to;
    }

    /**
     * @param $enrollableId
     * @param $surveyInstanceId
     *
     * @return array
     */
    public function getSurveyAnswersEnrollables($enrollableId, $surveyInstanceId)
    {
        $address = $this->getAnswerForQuestionUsingIdentifier($enrollableId, $surveyInstanceId, 'Q_CONFIRM_ADDRESS')
            ->mapWithKeys(function ($answerValues) {
                return collect($answerValues)->mapWithKeys(function ($answerWithKey) {
                    return $answerWithKey;
                });
            });

        return [
            'email'            => $this->getAnswerForQuestionUsingIdentifier($enrollableId, $surveyInstanceId, 'Q_CONFIRM_EMAIL')->flatten(),
            'preferred_number' => $this->getAnswerForQuestionUsingIdentifier($enrollableId, $surveyInstanceId, 'Q_PREFERRED_NUMBER')->flatten(),
            'preferred_days'   => $this->getAnswerForQuestionUsingIdentifier($enrollableId, $surveyInstanceId, 'Q_PREFERRED_DAYS')->flatten(),
            'preferred_time'   => $this->getAnswerForQuestionUsingIdentifier($enrollableId, $surveyInstanceId, 'Q_PREFERRED_TIME')->flatten(),
            'requests_info'    => $this->getAnswerForQuestionUsingIdentifier($enrollableId, $surveyInstanceId, 'Q_REQUESTS_INFO')->flatten(),
            'address'          => $address->toArray(),
            //            'dob'                 => $this->getAnswerForQuestionUsingIdentifier($enrollableId, $surveyInstanceId, 'Q_DOB')[6],
            'confirm_letter_read' => ! empty($confirmLetterRead = $this->getAnswerForQuestionUsingIdentifier($enrollableId, $surveyInstanceId, 'Q_CONFIRM_LETTER'))
                ? $confirmLetterRead->flatten()
                : '',
        ];
    }

    /**
     * @throws \Exception
     *
     * //@todo:Should split the functionality of this class into two more classes
     * //@todo:One for Enrollees and one for unreachable patients
     *
     * @return string
     */
    public function handle()
    {
        $enrollableId     = is_json($this->data) ? json_decode($this->data)->enrollable_id : $this->data['enrollable_id'];
        $surveyInstanceId = is_json($this->data) ? json_decode($this->data)->survey_instance_id : $this->data['survey_instance_id'];
        $surveyAnswers    = $this->getSurveyAnswersEnrollables($enrollableId, $surveyInstanceId);
        $user             = User::withTrashed()->whereId($enrollableId)->firstOrFail();
        $addressData      = $surveyAnswers['address'];
        $emailToString    = $this->getEmail($surveyAnswers['email'], $user->email);
//        $emailToString               = getStringValueFromAnswerAwvUser($surveyAnswers['email']);
        $preferredContactDays        = $this->getPreferredDaysToString($surveyAnswers['preferred_days']);
        $patientContactTimesToString = $this->getPreferredContactHoursToString($surveyAnswers['preferred_time']);
        $preferredPhoneNumber        = getStringValueFromAnswerAwvUser($surveyAnswers['preferred_number']);
        if (empty($preferredContactDays) || empty($patientContactTimesToString)) {
            throw new \Exception("Missing survey values for user [$user->id]");
        }

        $preferredContactDaysToArray = explode(',', $preferredContactDays);
        $patientContactTimesArray    = explode(' ', $patientContactTimesToString);

        $patientContactTimeStart = Carbon::parse($patientContactTimesArray[0])->toTimeString();
        $patientContactTimeEnd   = Carbon::parse($patientContactTimesArray[2])->toTimeString();

        if ($user->hasRole('survey-only')) {
            $enrollee = Enrollee::whereUserId($user->id)->first();
            if ( ! $enrollee) {
                Log::critical("Enrolle with user_id[$user->id] not found");

                return;
            }

            $enrollee->update([
                'primary_phone'             => $preferredPhoneNumber,
                'preferred_days'            => $preferredContactDays,
                'preferred_window'          => $patientContactTimesToString,
                'address'                   => $addressData['address'],
                'city'                      => $addressData['city'],
                'state'                     => $addressData['state'],
                'zip'                       => $addressData['zip'],
                'email'                     => $emailToString,
                'consented_at'              => now(),
                'status'                    => Enrollee::CONSENTED,
                'auto_enrollment_triggered' => true,
            ]);
//    It's Duplication but better to make sense. Will refactor later

            $user->update([
                'address' => $addressData['address'],
                'city'    => $addressData['city'],
                'state'   => $addressData['state'],
                'zip'     => $addressData['zip'],
                'email'   => $emailToString,
            ]);

            $this->updateEnrolleePatient(
                $user,
                $preferredContactDays,
                $patientContactTimeStart,
                $patientContactTimeEnd,
                $preferredPhoneNumber,
                $preferredContactDaysToArray
            );

            ImportConsentedEnrollees::dispatch([$enrollee->id]);

            $patientType = 'Initial';
            $id          = $enrollee->id;
        } else {
            $this->updateUserModel($user, $addressData);
            $this->updatePatientPhoneNumber($user, $preferredPhoneNumber);
            $this->updatePatientInfo($user, $preferredContactDays, $patientContactTimeStart, $patientContactTimeEnd);
            $this->updatePatientContactWindow($user, $preferredContactDaysToArray, $patientContactTimeStart, $patientContactTimeEnd);
            $this->reEnrollUnreachablePatient($user);
            $this->updateEnrolleAvatarModel($user->id);

            $patientType = 'Unreachable';
            $id          = $user->id;
        }

        return info("$patientType patient $id has been enrolled");
    }

    public function reEnrollUnreachablePatient(User $user)
    {
        $user->patientInfo->update([
            'ccm_status' => Patient::ENROLLED,
        ]);
    }

    /**
     * @param null  $answer
     * @param array $default
     *
     * @return array|mixed
     */
    public function sanitizedValue($answer = null, $default = [])
    {
        if ( ! $answer) {
            return $default;
        }

        $answerVal = json_decode($answer->value, true);
        $answers   = [];

        if (is_string($answerVal)) {
            return $answerVal;
        }

        if (is_bool($answerVal)) {
            return $answerVal;
        }

        if (is_array($answerVal)) {
            foreach ($answerVal as $value) {
                if (isset($value->name)) {
                    $answers[] = $value->name;
                } elseif (isset($value->value)) {
                    $answers[] = $value->value;
                } else {
                    $answers[] = $value;
                }
            }

            return $answers;
        }

        if ($answerVal && isset($answerVal->value)) {
            return $answerVal->value;
        }

//        else return []
        return $default;
    }

    private function getEmail(object $answerEmail, $userEmail)
    {
        $answerEmail = getStringValueFromAnswerAwvUser($answerEmail);

        if (empty($answerEmail)) {
            return $userEmail;
        }

        return  $answerEmail;
    }

    /**
     * @return \Collection|Collection
     */
    private function getSanitizedAnswerValue(Collection $enrolleesQuestions, Collection $enrollableSurveyData, string $identifier)
    {
        return collect($enrolleesQuestions)->where('identifier', '=', $identifier)
            ->transform(function ($question) use ($enrollableSurveyData) {
                $answer = collect($enrollableSurveyData)->where('question_id', $question->id)->first();

                return $this->sanitizedValue($answer);
            });
    }

    private function updateEnrolleAvatarModel($userId)
    {
        Enrollee::whereUserId($userId)->update([
            'status'                    => Enrollee::ENROLLED,
            'auto_enrollment_triggered' => true,
        ]);
    }

    private function updateEnrolleePatient(
        User $user,
        $preferredContactDays,
        $patientContactTimeStart,
        $patientContactTimeEnd,
        $preferredPhoneNumber,
        $preferredContactDaysToArray
    ) {
        $this->updatePatientPhoneNumber($user, $preferredPhoneNumber);
        $this->updatePatientContactWindow($user, $preferredContactDaysToArray, $patientContactTimeStart, $patientContactTimeEnd);
        $user->patientInfo->update([
            'preferred_cc_contact_days'  => $preferredContactDays,
            'daily_contact_window_start' => $patientContactTimeStart,
            'daily_contact_window_end'   => $patientContactTimeEnd,
            'auto_enrollment_triggered'  => true,
            'ccm_status'                 => Patient::ENROLLED,
        ]);
    }

    private function updatePatientContactWindow(User $user, $preferredContactDaysToArray, $patientContactTimeStart, $patientContactTimeEnd)
    {
        foreach ($preferredContactDaysToArray as $dayOfWeek) {
            $user->patientInfo->contactWindows()->updateOrCreate([
                'day_of_week'       => $dayOfWeek,
                'window_time_start' => $patientContactTimeStart,
                'window_time_end'   => $patientContactTimeEnd,
            ]);
        }
    }

    /**
     * @param $preferredContactDays
     * @param $patientContactTimeStart
     * @param $patientContactTimeEnd
     * @param $dob
     */
    private function updatePatientInfo(User $user, $preferredContactDays, $patientContactTimeStart, $patientContactTimeEnd)
    {
        $user->patientInfo->update([
            'preferred_cc_contact_days'  => $preferredContactDays,
            'daily_contact_window_start' => $patientContactTimeStart,
            'daily_contact_window_end'   => $patientContactTimeEnd,
            'auto_enrollment_triggered'  => true,
        ]);
    }

    /**
     * @param $preferredPhoneNumber
     */
    private function updatePatientPhoneNumber(User $user, $preferredPhoneNumber)
    {
        $user->phoneNumbers()->updateOrCreate(
            [
                'user_id' => $user->id,
            ],
            [
                'is_primary' => true,
                'number'     => $preferredPhoneNumber,
            ]
        );
    }

    /**
     * @param $addressData
     */
    private function updateUserModel(User $user, $addressData)
    {
        $user->update([
            'address' => $addressData['address'],
            'city'    => $addressData['city'],
            'state'   => $addressData['state'],
            'zip'     => $addressData['zip'],
        ]);
    }
}
