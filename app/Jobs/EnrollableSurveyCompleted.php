<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\Jobs\ImportConsEnrolleesJustForQa;
use CircleLinkHealth\Eligibility\ValueObjects\SurveyOnlyEnrolleeMedicalRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class EnrollableSurveyCompleted implements ShouldQueue
{
    use Dispatchable;
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

    /**
     * @return string
     */
    public function createHash(Enrollee $enrollee)
    {
        return $enrollee->practice->name.$enrollee->first_name.$enrollee->last_name.$enrollee->mrn.$enrollee->city.$enrollee->state.$enrollee->zip;
    }

    public function deleteBatch($userId)
    {
        EligibilityBatch::whereInitiatorId($userId)->forceDelete();
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
        $surveyInstance     = DB::table('survey_instances')->where('id', '=', $surveyInstanceId)->first();
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
     * @param $val
     *
     * @return mixed
     */
    public function getArrayValue($val)
    {
        return $val[0];
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
        return [
            'dob'                 => $this->getAnswerForQuestionUsingIdentifier($enrollableId, $surveyInstanceId, 'Q_DOB')[0],
            'preferred_number'    => $this->getAnswerForQuestionUsingIdentifier($enrollableId, $surveyInstanceId, 'Q_PREFERRED_NUMBER')[1],
            'preferred_days'      => $this->getAnswerForQuestionUsingIdentifier($enrollableId, $surveyInstanceId, 'Q_PREFERRED_DAYS')[2],
            'preferred_time'      => $this->getAnswerForQuestionUsingIdentifier($enrollableId, $surveyInstanceId, 'Q_PREFERRED_TIME')[3],
            'requests_info'       => $this->getAnswerForQuestionUsingIdentifier($enrollableId, $surveyInstanceId, 'Q_REQUESTS_INFO')[4][0],
            'address'             => $this->getAnswerForQuestionUsingIdentifier($enrollableId, $surveyInstanceId, 'Q_CONFIRM_ADDRESS')[5],
            'email'               => $this->getAnswerForQuestionUsingIdentifier($enrollableId, $surveyInstanceId, 'Q_CONFIRM_EMAIL')[6],
            'confirm_letter_read' => $this->getAnswerForQuestionUsingIdentifier($enrollableId, $surveyInstanceId, 'Q_CONFIRM_LETTER')[7][0],
        ];
    }

    /**
     * @throws \Exception
     *
     * @return string
     */
    public function handle()
    {
        $enrollableId     = $this->data['enrollable_id'];
        $surveyInstanceId = $this->data['survey_instance_id'];

        $surveyAnswers = $this->getSurveyAnswersEnrollables($enrollableId, $surveyInstanceId);
        $user          = User::withTrashed()->whereId($enrollableId)->firstOrFail();

        $isSurveyOnly = $user->hasRole('survey-only');

        if ($isSurveyOnly) {
            $enrollee = Enrollee::whereUserId($user->id)->firstOrFail();
            $enrollee->update([
                'dob'              => $surveyAnswers['dob'],
                'primary_phone'    => $surveyAnswers['preferred_number'],
                'preferred_days'   => $this->getPreferredDaysToString($surveyAnswers['preferred_days']),
                'preferred_window' => $this->getPreferredTimesToString($surveyAnswers['preferred_time']),
                'address'          => $surveyAnswers['address'],
                'email'            => $surveyAnswers['email'],
                'status'           => Enrollee::ENROLLED,
            ]);

           if (App::environment(['local', 'review'])){
               $this->importEnrolleeSurveyOnly($enrollee, $user);
           }
            $patientType = 'Initial';
            $id          = $enrollee->id;
        } else {
            $this->reEnrollUnreachablePatient($user);
            $patientType = 'Unreachable';
            $id          = $user->id;
        }

        return info("$patientType patient $id has been enrolled");
    }

    /**
     * @param $enrollee
     *
     * @throws \Exception
     */
    public function importEnrolleeSurveyOnly($enrollee, User $user)
    {
        $job              = new EligibilityJob();
        $practice         = Practice::whereId($enrollee->practice_id)->first();
        $medicalRecord    = (new SurveyOnlyEnrolleeMedicalRecord($job, $practice))->createFromSurveyOnlyUser($enrollee);
        $eligibilityBatch = $this->updateOrCreateBatch($user, $practice);
        $hash             = $this->createHash($enrollee);
        $this->updateOrCreateEligibilityJob($eligibilityBatch, $medicalRecord, $hash);

        $user->delete();
        ImportConsEnrolleesJustForQa::dispatch([$enrollee->id]);
//        $this->deleteBatch($user->id);
//        $user->forceDelete();
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
        $answerVal = json_decode($answer->value);
        $answers   = [];
        if (is_string($answerVal)) {
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
        if (array_key_exists('value', $answerVal)) {
            return $answerVal->value;
        }

//        else return []
        return $default;
    }

    /**
     * @param $user
     * @param $practice
     *
     * @return EligibilityBatch|\Illuminate\Database\Eloquent\Model
     */
    public function updateOrCreateBatch($user, $practice)
    {
        return EligibilityBatch::updateOrCreate(
            [
                'practice_id' => $practice->id,
                'type'        => 'survey_only',
            ],
            [
                'status' => EligibilityBatch::STATUSES['complete'],
            ]
        );
    }

    /**
     * @param $eligibilityBatch
     * @param $medicalRecord
     * @param $hash
     */
    public function updateOrCreateEligibilityJob($eligibilityBatch, $medicalRecord, $hash)
    {
        EligibilityJob::updateOrCreate(
            [
                'batch_id' => $eligibilityBatch->id,
            ],
            [
                'data' => $medicalRecord,
                'hash' => $hash,
            ]
        );
    }
}
