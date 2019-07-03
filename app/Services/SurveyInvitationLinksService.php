<?php


namespace App\Services;

use App\InvitationLink;
use App\Survey;
use App\SurveyInstance;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;

class SurveyInvitationLinksService
{
    /**
     * @param $userId
     * @param string $forYear
     * @param bool $addUserToSurveyInstance
     *
     * @return string
     * @throws \Exception
     */
    public function createAndSaveUrl($userId, string $forYear, bool $addUserToSurveyInstance = false)
    {
        $user = User
            ::with([
                'patientInfo',
                'surveyInstances' => function ($query) {
                    $query->ofSurvey(Survey::HRA)->current();
                },
            ])
            ->where('id', '=', $userId)
            ->firstOrFail();

        if (!$user->patientInfo) {
            throw new \Exception("missing patient info from user");
        }

        $patientInfoId = $user->patientInfo->id;
        $this->expireAllPastUrls($patientInfoId);

        if ($user->surveyInstances->isEmpty()) {

            if ( ! $addUserToSurveyInstance) {
                throw new \Exception("user does not belong to a survey instance");
            }

            $hraSurvey = Survey
                ::with([
                    'instances' => function ($instance) use ($forYear) {
                        $instance->forYear($forYear);
                    },
                ])
                ->where('name', Survey::HRA)
                ->firstOrFail();

            if ($hraSurvey->instances->isEmpty()) {
                throw new \Exception("There is no HRA survey instance for year $forYear");
            }

            $user->surveys()
                 ->attach($hraSurvey->id, [
                     'survey_instance_id' => $hraSurvey->instances->first()->id,
                     'status'             => SurveyInstance::PENDING,
                 ]);

            $surveyId = $hraSurvey->id;

        } else {

            /** @var SurveyInstance */
            $hraSurveyInstance = $user->surveyInstances->first();

            if ($hraSurveyInstance->pivot->status === SurveyInstance::COMPLETED) {
                throw new \Exception("cannot create invitation link for a completed survey");
            }

            $surveyId = $hraSurveyInstance->survey_id;
        }

        //APP_URL must be set correctly in .env for this to work
        $url = URL::signedRoute('auth.login.signed',
            [
                'user'      => $userId,
                'survey'    => $surveyId,

                //added this so it will generate a new url every time
                'timestamp' => Carbon::now()->timestamp,
            ]);

        $urlToken = $this->parseUrl($url);

        InvitationLink::create([
            'patient_info_id'     => $patientInfoId,
            'survey_id'           => $surveyId,
            'link_token'          => $urlToken,
            'is_manually_expired' => false,
        ]);

        return $url;
    }

    public function expireAllPastUrls($patientInfoId)
    {
        InvitationLink::where('patient_info_id', $patientInfoId)
                      ->where('is_manually_expired', '=', 0)
                      ->update(['is_manually_expired' => true]);
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

        $urlToken = $output['signature'];

        return $urlToken;
    }

    public function getPatientPhoneNumberById($userId)
    {//im using User model cause eventually this method will accept user-names also.
        $user = User::with([
            'phoneNumber' => function ($q) use ($userId) {
                $q->where('user_id', $userId);
            },
        ])
                    ->where('id', $userId)
                    ->firstOrFail();

        $phoneNumber = $user->phoneNumber->number;

        return $phoneNumber;
    }
}
