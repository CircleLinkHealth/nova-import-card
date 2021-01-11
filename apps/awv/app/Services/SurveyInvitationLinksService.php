<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\InvitationLink;
use App\Jobs\SurveyAnswersCalculateSuggestionsJob;
use App\Survey;
use App\SurveyInstance;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Waavi\UrlShortener\Facades\UrlShortener;

class SurveyInvitationLinksService
{
    /**
     * @param string $forYear
     *
     * @throws \Exception
     * @return string
     */
    public function createAndSaveUrl(
        User $user,
        string $surveyName,
        bool $addUserToSurveyInstance = false
    ) {
        if ( ! $user->patientInfo) {
            throw new \Exception('missing patient info from user');
        }

        if ($user->surveyInstances->isEmpty()) {
            if ( ! $addUserToSurveyInstance) {
                throw new \Exception('user does not belong to a survey instance');
            }

            $ids      = $this->enrolUser($user);
            $surveyId = $ids[$surveyName];
        } else {
            /** @var SurveyInstance */
            $hraSurveyInstance = $user->surveyInstances->first();

            if (SurveyInstance::COMPLETED === $hraSurveyInstance->pivot->status) {
                throw new \Exception('cannot create invitation link for a completed survey');
            }

            $surveyId = $hraSurveyInstance->survey_id;
        }

        $url      = null;
        $shortUrl = null;
        if (Survey::HRA === $surveyName || Survey::ENROLLEES === $surveyName) {
            $patientInfoId = $user->patientInfo->id;
            $this->expireAllPastUrls($patientInfoId, $surveyId);

            //APP_URL must be set correctly in .env for this to work
            $url = URL::signedRoute(
                'auth.login.signed',
                [
                    'user'   => $user->id,
                    'survey' => $surveyId,

                    //added this so it will generate a new url every time
                    'timestamp' => Carbon::now()->timestamp,
                ]
            );

            try {
                $shortUrl = UrlShortener::shorten($url);
            } catch (\Exception $e) {
                \Log::warning($e->getMessage());
            }

            $urlToken = $this->parseUrl($url);

            InvitationLink::create([
                'patient_info_id'     => $patientInfoId,
                'survey_id'           => $surveyId,
                'link_token'          => $urlToken,
                'is_manually_expired' => false,
                'url'                 => $url,
                'short_url'           => $shortUrl,
            ]);
        } else {
            $url = route('survey.vitals', [
                'patientId' => $user->id,
            ]);
        }

        return $shortUrl ?? $url;
    }

    /**
     * @throws \Exception
     * @return array
     */
    public function enrolUser(User $user)
    {
        $surveys = Survey
            ::with([
                'instances' => function ($instance) {
                    $instance->mostRecent();
                },
            ])
                ->get();

        $hraSurvey = $surveys->firstWhere('name', Survey::HRA);
        if ( ! $hraSurvey || $hraSurvey->instances->isEmpty()) {
            throw new \Exception('There is no HRA survey instance.');
        }

        $vitalsSurvey = $surveys->firstWhere('name', Survey::VITALS);
        if ( ! $vitalsSurvey || $vitalsSurvey->instances->isEmpty()) {
            throw new \Exception('There is no VITALS survey instance.');
        }

        if ($user->surveyInstances->where('survey_id', '=', $vitalsSurvey->id)->isEmpty()) {
            $user->surveys()
                ->attach(
                    $vitalsSurvey->id,
                    [
                        'survey_instance_id' => $vitalsSurvey->instances->first()->id,
                        'status'             => SurveyInstance::PENDING,
                    ]
                );
        }

        if ($user->surveyInstances->where('survey_id', '=', $hraSurvey->id)->isEmpty()) {
            $user->surveys()
                ->attach($hraSurvey->id, [
                    'survey_instance_id' => $hraSurvey->instances->first()->id,
                    'status'             => SurveyInstance::PENDING,
                ]);
        }

        //in case job runs synchronously
        try {
            SurveyAnswersCalculateSuggestionsJob::dispatch($user->id)->onQueue('awv-high');
        } catch (\Exception $exception) {
            \Log::error($exception->getMessage());
        }

        return [
            Survey::HRA    => $hraSurvey->id,
            Survey::VITALS => $vitalsSurvey->id,
        ];
    }

    /**
     * @param $userId
     *
     * @throws \Exception
     */
    public function enrolUserId($userId)
    {
        $user = User
            ::with([
                'patientInfo',
                'surveyInstances' => function ($query) {
                    $query->mostRecent();
                },
            ])
                ->where('id', '=', $userId)
                ->firstOrFail();

        $this->enrolUser($user);
    }

    public function expireAllPastUrls($patientInfoId, $surveyId = null)
    {
        InvitationLink::where('patient_info_id', $patientInfoId)
            ->where('is_manually_expired', '=', 0)
            ->when(null != $surveyId, function ($q) use ($surveyId) {
                $q->where('survey_id', '=', $surveyId);
            })
            ->update(['is_manually_expired' => true]);
    }

    public static function getPatientIdFromSignedUrl(string $url)
    {
        $parsed = parse_url($url);

        //http://awv.clh.test/auth/login-survey/326/3
        $path  = explode('/', $parsed['path']);
        $count = count($path);

        return $path[$count - 2];
    }

    public static function getSurveyIdFromSignedUrl(string $url)
    {
        $parsed = parse_url($url);

        //http://awv.clh.test/auth/login-survey/326/3
        $path = explode('/', $parsed['path']);

        return end($path);
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

        return $output['signature'];
    }
}
