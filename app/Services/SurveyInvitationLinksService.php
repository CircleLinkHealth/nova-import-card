<?php


namespace App\Services;

use App\InvitationLink;
use App\Survey;
use App\SurveyInstance;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class SurveyInvitationLinksService
{
    /**
     * @param User $user
     * @param string $surveyName
     * @param string $forYear
     * @param bool $addUserToSurveyInstance
     *
     * @return string
     * @throws \Exception
     */
    public function createAndSaveUrl(
        User $user,
        string $surveyName,
        string $forYear,
        bool $addUserToSurveyInstance = false
    ) {
        if ( ! $user->patientInfo) {
            throw new \Exception("missing patient info from user");
        }

        if ($user->surveyInstances->isEmpty()) {

            if ( ! $addUserToSurveyInstance) {
                throw new \Exception("user does not belong to a survey instance");
            }

            $ids      = $this->enrolUser($user, $forYear);
            $surveyId = $ids[$surveyName];

        } else {

            /** @var SurveyInstance */
            $hraSurveyInstance = $user->surveyInstances->first();

            if ($hraSurveyInstance->pivot->status === SurveyInstance::COMPLETED) {
                throw new \Exception("cannot create invitation link for a completed survey");
            }

            $surveyId = $hraSurveyInstance->survey_id;
        }

        $patientInfoId = $user->patientInfo->id;
        $this->expireAllPastUrls($patientInfoId, $surveyId);

        //APP_URL must be set correctly in .env for this to work
        $url = URL::signedRoute('auth.login.signed',
            [
                'user'      => $user->id,
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

    public function expireAllPastUrls($patientInfoId, $surveyId = null)
    {
        InvitationLink::where('patient_info_id', $patientInfoId)
                      ->where('is_manually_expired', '=', 0)
                      ->when($surveyId != null, function ($q) use ($surveyId) {
                          $q->where('survey_id', '=', $surveyId);
                      })
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

    /**
     * @param User $user
     * @param string|null $forYear
     *
     * @return array
     * @throws \Exception
     */
    public function enrolUser(User $user, string $forYear = null)
    {

        if ( ! $forYear) {
            $forYear = Carbon::now()->year;
        }

        $surveys = Survey
            ::with([
                'instances' => function ($instance) use ($forYear) {
                    $instance->forYear($forYear);
                },
            ])
            ->get();

        $hraSurvey = $surveys->firstWhere('name', Survey::HRA);
        if ( ! $hraSurvey || $hraSurvey->instances->isEmpty()) {
            throw new \Exception("There is no HRA survey instance for year $forYear");
        }

        $vitalsSurvey = $surveys->firstWhere('name', Survey::VITALS);
        if ( ! $vitalsSurvey || $vitalsSurvey->instances->isEmpty()) {
            throw new \Exception("There is no VITALS survey instance for year $forYear");
        }

        $user->surveys()
             ->attach($vitalsSurvey->id, [
                     'survey_instance_id' => $vitalsSurvey->instances->first()->id,
                     'status'             => SurveyInstance::PENDING,
                 ]
             );

        $user->surveys()
             ->attach($hraSurvey->id, [
                 'survey_instance_id' => $hraSurvey->instances->first()->id,
                 'status'             => SurveyInstance::PENDING,
             ]);

        return [
            Survey::HRA    => $hraSurvey->id,
            Survey::VITALS => $vitalsSurvey->id,
        ];
    }
}
