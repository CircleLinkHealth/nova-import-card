<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Traits\EnrollableManagement;
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
use Illuminate\Support\Facades\DB;

class EnrollableSurveyCompleted implements ShouldQueue
{
    use Dispatchable;
    use EnrollableManagement;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

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
     * @param $val
     *
     * @return mixed
     */
    public function getArrayValue($val)
    {
        return $val[0];
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function handle()
    {
        $enrollableId = is_json($this->data) ? json_decode($this->data)->enrollable_id : $this->data['enrollable_id'];
        $surveyInstanceId = is_json($this->data) ? json_decode($this->data)->survey_instance_id : $this->data['survey_instance_id'];
        $surveyAnswers = $this->getSurveyAnswersEnrollables($enrollableId, $surveyInstanceId);
        $user = User::withTrashed()->whereId($enrollableId)->firstOrFail();
        $isSurveyOnly = $user->hasRole('survey-only');
        $addressData = $this->getAddressData($surveyAnswers['address']);
        $dob = Carbon::parse($surveyAnswers['dob'])->toDateString();

        if ($isSurveyOnly) {
            $enrollee = Enrollee::whereUserId($user->id)->firstOrFail();
            $enrollee->update([
                'dob' => $dob,
                'primary_phone' => $surveyAnswers['preferred_number'],
                'preferred_days' => $this->getPreferredDaysToString($surveyAnswers['preferred_days']),
                'preferred_window' => $this->getPreferredTimesToString($surveyAnswers['preferred_time']),
                'address' => $addressData['address'],
                'city' => $addressData['city'],
                'state' => $addressData['state'],
                'zip' => $addressData['zip'],
                'email' => $surveyAnswers['email'],
                'status' => Enrollee::ENROLLED,
                'auto_enrollment_triggered' => true,
                'user_id' => null,
            ]);

            $this->importEnrolleeSurveyOnly($enrollee, $user);

            $patientType = 'Initial';
            $id = $enrollee->id;
        } else {
            $preferredContactDays = $this->getPreferredDaysToString($surveyAnswers['preferred_days']);
            $preferredContactDaysToArray = explode(',', $preferredContactDays);
            $patientContactTimeStart = Carbon::parse($surveyAnswers['preferred_time'][0]->from)->toTimeString();
            $patientContactTimeEnd = Carbon::parse($surveyAnswers['preferred_time'][0]->to)->toTimeString();
            $this->updateUserModel($user, $addressData);
            $this->updatePatientPhoneNumber($user, $surveyAnswers['preferred_number']);
            $this->upatePatientInfo($user, $preferredContactDays, $patientContactTimeStart, $patientContactTimeEnd, $dob);
            $this->updatePatientContactWindow($user, $preferredContactDaysToArray, $patientContactTimeStart, $patientContactTimeEnd);
            $this->reEnrollUnreachablePatient($user);

            $patientType = 'Unreachable';
            $id = $user->id;
        }

        return info("$patientType patient $id has been enrolled");
    }

    /**
     * @param $enrollableId
     * @param $surveyInstanceId
     *
     * @return array
     */
    public function getSurveyAnswersEnrollables($enrollableId, $surveyInstanceId)
    {
        return [
            'email' => $this->getAnswerForQuestionUsingIdentifier($enrollableId, $surveyInstanceId, 'Q_CONFIRM_EMAIL')[0],
            'preferred_number' => $this->getAnswerForQuestionUsingIdentifier($enrollableId, $surveyInstanceId, 'Q_PREFERRED_NUMBER')[1],
            'preferred_days' => $this->getAnswerForQuestionUsingIdentifier($enrollableId, $surveyInstanceId, 'Q_PREFERRED_DAYS')[2],
            'preferred_time' => $this->getAnswerForQuestionUsingIdentifier($enrollableId, $surveyInstanceId, 'Q_PREFERRED_TIME')[3],
            'requests_info' => $this->getAnswerForQuestionUsingIdentifier($enrollableId, $surveyInstanceId, 'Q_REQUESTS_INFO')[4],
            'address' => $this->getAnswerForQuestionUsingIdentifier($enrollableId, $surveyInstanceId, 'Q_CONFIRM_ADDRESS')[5],
            'dob' => $this->getAnswerForQuestionUsingIdentifier($enrollableId, $surveyInstanceId, 'Q_DOB')[6],
            'confirm_letter_read' => !empty($this->getAnswerForQuestionUsingIdentifier($enrollableId, $surveyInstanceId, 'Q_CONFIRM_LETTER')[7])
                ? $this->getAnswerForQuestionUsingIdentifier($enrollableId, $surveyInstanceId, 'Q_CONFIRM_LETTER')[7][0]
                : '',
        ];
    }

    /**
     * @param $enrollableId
     * @param $surveyInstanceId
     * @param $identifier
     *
     * @return array
     */
    public function getAnswerForQuestionUsingIdentifier($enrollableId, $surveyInstanceId, $identifier)
    {
        $surveyInstance = DB::table('survey_instances')->where('id', '=', $surveyInstanceId)->first();
        $enrolleesQuestions = DB::table('questions')->where('survey_id', '=', $surveyInstance->survey_id)->get();

        $enrollableSurveyData = DB::table('answers')
            ->where('user_id', '=', $enrollableId)
            ->where('survey_instance_id', $surveyInstanceId)->get();

        return collect($enrolleesQuestions)->where('identifier', '=', $identifier)
            ->transform(function ($question) use ($enrollableSurveyData) {
                $answer = collect($enrollableSurveyData)->where('question_id', $question->id)->first();

                return $this->sanitizedValue($answer);
            })->toArray();
    }

    /**
     * @param null $answer
     * @param array $default
     *
     * @return array|mixed
     */
    public function sanitizedValue($answer = null, $default = [])
    {
        if (!$answer) {
            return $default;
        }

        $answerVal = json_decode($answer->value);
        $answers = [];

        if (is_string($answerVal)) {
            return $answerVal;
        }

        if (is_bool($answerVal)) {
            return $answerVal;
        }

        if (is_array($answerVal)) {
            foreach ($answerVal as $value) {
                if (array_key_exists('name', $value)) {
                    $answers[] = $value->name;
                } elseif (array_key_exists('value', $value)) {
                    $answers[] = $value->value;
                } else {
                    $answers[] = $value;
                }
            }

            return $answers;
        }

        if ($answerVal && array_key_exists('value', $answerVal)) {
            return $answerVal->value;
        }

//        else return []
        return $default;
    }

    /**
     * @param $address
     * @return array
     */
    public function getAddressData($address)
    {
        $addressData = [];
        foreach ($address as $value) {
            $addressData[array_keys(get_object_vars($value))[0]] = array_values(get_object_vars($value))[0];
        }

        return $addressData;
    }

    /**
     * @param $days
     *
     * @return string
     */
    public function getPreferredDaysToString($days)
    {
        $dow = [];
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
        return $times[0]->from . '-' . $times[0]->to;
    }

    /**
     * @param $enrollee
     *
     * @throws \Exception
     */
    public function importEnrolleeSurveyOnly($enrollee, User $user)
    {
        $user->delete();
        ImportConsentedEnrollees::dispatch([$enrollee->id]);
        $user->forceDelete();

//        $job = new EligibilityJob();
//        $practice = Practice::whereId($enrollee->practice_id)->first();
//        $medicalRecord = (new SurveyOnlyEnrolleeMedicalRecord($job, $practice))->createFromSurveyOnlyUser($enrollee);
//        $eligibilityBatch = $this->updateOrCreateBatch($user, $practice);
//        $hash = $this->createHash($enrollee);
//        $this->updateOrCreateEligibilityJob($eligibilityBatch, $medicalRecord, $hash);
//
//        $user->delete();
//        ImportConsEnrolleesJustForQa::dispatch([$enrollee->id]);
//        $this->deleteBatch($user->id);
//        $user->forceDelete();
    }

    /**
     * @param $addressData
     */
    private function updateUserModel(User $user, $addressData)
    {
        $user->update([
            'address' => $addressData['address'],
            'city' => $addressData['city'],
            'state' => $addressData['state'],
            'zip' => $addressData['zip'],
        ]);
    }

    /**
     * @param $preferredNumber
     */
    private function updatePatientPhoneNumber(User $user, $preferredNumber)
    {
        $user->phoneNumbers()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'is_primary' => true,
                'number' => $preferredNumber,
            ]
        );
    }

    /**
     * @param $preferredContactDays
     * @param $patientContactTimeStart
     * @param $patientContactTimeEnd
     * @param $dob
     */
    private function upatePatientInfo(User $user, $preferredContactDays, $patientContactTimeStart, $patientContactTimeEnd, $dob)
    {
        $user->patientInfo->update([
            'birth_date' => $dob,
            'preferred_cc_contact_days' => $preferredContactDays,
            'daily_contact_window_start' => $patientContactTimeStart,
            'daily_contact_window_end' => $patientContactTimeEnd,
            'auto_enrollment_triggered' => true,
        ]);
    }

    private function updatePatientContactWindow(User $user, $preferredContactDaysToArray, $patientContactTimeStart, $patientContactTimeEnd)
    {
        foreach ($preferredContactDaysToArray as $dayOfWeek) {
            $user->patientInfo->contactWindows()->updateOrCreate([
                'day_of_week' => $dayOfWeek,
                'window_time_start' => $patientContactTimeStart,
                'window_time_end' => $patientContactTimeEnd,
            ]);
        }
    }

    public function reEnrollUnreachablePatient(User $user)
    {
//        Im no showing this info anywhere. Do i need to show them anywhere?
        // @todo:Ask Ethan should i assign this to nurse Ethan ()
        $user->patientInfo->update([
            'ccm_status' => Patient::ENROLLED,
        ]);
    }
}
