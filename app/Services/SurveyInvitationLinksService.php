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
    const SMS_TEXT_FOR_KNOWN_APPOINTMENT_DATE_TIME = "Hello! Dr. {primaryPhysicianLastName} requests you complete this wellness survey before your scheduled appointment on {date}[“mm/dd/yy”] at {time}[hh:mm am/pm].";
    const SMS_TEXT_FOR_KNOWN_APPOINTMENT_DATE_ONLY = "Hello! Dr. {primaryPhysicianLastName} requests you complete this wellness survey before your scheduled appointment on {date}[“mm/dd/yy”].";
    const SMS_TEXT_FOR_UNKNOWN_APPOINTMENT_DATE = "Hello! Dr. {primaryPhysicianLastName} at {practiceName} requests you complete this health survey as soon as you can. Please call {clhNumber} if you have any questions.";

    /**
     * @param User $user
     * @param string $forYear
     * @param bool $addUserToSurveyInstance
     *
     * @return string
     * @throws \Exception
     */
    public function createAndSaveUrl(User $user, string $forYear, bool $addUserToSurveyInstance = false)
    {
        if ( ! $user->patientInfo) {
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

    /**
     * @param User $user
     * @param string $url
     *
     * @return string
     */
    public function getSMSText(User $user, string $url)
    {
        $providerLastName = $user->billingProviderUser()->last_name;
        $practiceName     = $user->primaryPractice->display_name;

        //todo: check if we have known appointment and select appropriate SMS message
        $text = Str::replaceFirst("{primaryPhysicianLastName}", $providerLastName,
            self::SMS_TEXT_FOR_UNKNOWN_APPOINTMENT_DATE);
        $text = Str::replaceFirst("{practiceName}", $practiceName, $text);
        $text = Str::replaceFirst("{clhNumber}", config('services.twilio.from'), $text);
        $text = $text . "\n" . $url;

        return $text;
    }
}
