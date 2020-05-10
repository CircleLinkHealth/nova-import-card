<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Support\Facades\Log;

trait EnrollableNotificationContent
{
    /**
     * @param $notifiable
     * @param $isReminder
     *
     * @throws \Exception
     *
     * @return array|string
     */
    public function emailAndSmsContent(User $notifiable, $isReminder)
    {
        $enrollableEmailContent = $this->getEmailContent($notifiable, $isReminder);
        $providerName           = $enrollableEmailContent['providerLastName'];
        $practiceName           = $enrollableEmailContent['practiceName'];
        $line2                  = $enrollableEmailContent['line2'];
        $isSurveyOnly           = $enrollableEmailContent['isSurveyOnly'];

        if (empty($practiceName)) {
            Log::warning("Practice name not found for user $notifiable->id");
            $practiceName = '???';
        }

        $line1 = "Hi, it's Dr. $providerName's office at $practiceName! ";

        if (empty($providerName)) {
            $line1 = "Hi, it's your Provider's office at $practiceName! ";
        }

        $urlData = [
            'enrollable_id'  => $notifiable->id,
            'is_survey_only' => $isSurveyOnly,
        ];

        return [
            'line1'   => $line1,
            'line2'   => $line2,
            'urlData' => $urlData,
        ];
    }

    /**
     * @param $notifiable
     * @param bool $isReminder
     *
     * @throws \Exception
     *
     * @return array
     */
    public function getEmailContent(User $notifiable, $isReminder = false)
    {
        $notifiableIsSurveyOnly = $notifiable->checkForSurveyOnlyRole();

        return $notifiableIsSurveyOnly
            ? $this->getEnrolleeEmailContent($notifiable, $isReminder)
            : $this->getUserEmailContent($notifiable, $isReminder);
    }

    /**
     * Should only pass User model which has enrollee -> user_id.
     *
     * @param $notifiable
     * @param $isReminder
     *
     * @throws \Exception
     *
     * @return array
     */
    public function getEnrolleeEmailContent(User $notifiable, $isReminder)
    {
        /** @var Enrollee $enrollee */
        $enrollee = Enrollee::with([
            'provider' => function ($q) {
                return $q->select(['id', 'last_name']);
            },
            'practice' => function ($q) {
                return $q->select(['id', 'display_name']);
            },
        ])
            ->whereUserId($notifiable->id)->first();

        if ( ! $enrollee) {
            throw new \Exception("could not find enrollee for user[$notifiable->id]");
        }

        if ( ! $enrollee->provider) {
            throw new \Exception("could not find provider for enrollee[$enrollee->id]");
        }

        if ( ! $enrollee->practice) {
            throw new \Exception("could not find practice for enrollee[$enrollee->id]");
        }

        $providerLastName = $enrollee->provider->last_name;
        $line2            = $isReminder
            ? "Just circling back on Dr. $providerLastName's new Personalized Care program. Please enroll or get more info here: "
            : "Dr. $providerLastName has invested in a new wellness program for you. Please enroll or get more info here: ";

        return [
            'providerLastName' => $providerLastName,
            'practiceName'     => $enrollee->practice->display_name,
            'line2'            => $line2,
            'isSurveyOnly'     => true,
        ];
    }

    /**
     * @param $notifiable
     * @param $isReminder
     *
     * @return array
     */
    public function getUserEmailContent(User $notifiable, $isReminder)
    {
        $notifiable->loadMissing([
            'billingProvider',
            'patientInfo',
            'primaryPractice' => function ($q) {
                return $q->select(['id', 'display_name']);
            },
        ]);

        $provider                       = $notifiable->billingProviderUser();
        $lastNurseThatPerformedActivity = $notifiable->patientInfo->lastNurseThatPerformedActivity();
        $nurseFirstName                 = ! empty($lastNurseThatPerformedActivity) ? $lastNurseThatPerformedActivity->user->display_name
            : '';

        if ( ! empty($nurseFirstName)) {
            $line2 = $isReminder
                ? "Just circling back because $nurseFirstName, our telephone nurse was unable to reach you this month. Please re-start calls in this link: "
                : "$nurseFirstName, our nurse, was unable to reach you this month. Please re-start calls in this link: ";
        } else {
            // Should never be empty with production data.
            $line2 = $isReminder
                ? 'Just circling back because our telephone nurse, was unable to reach you this month. Please re-start calls in this link: '
                : 'Your Nurse Care Coach was unable to reach you this month. Please re-start calls in this link: ';
        }

        // Should never be empty with production data.
        if ( ! empty($provider)) {
            $providerLastName = $provider->last_name;
        } else {
            $providerLastName = '';
            Log::error("User $notifiable->id has null billingProviderUser");
        }

        return [
            'providerLastName' => $providerLastName,
            'nurseFirstName'   => $nurseFirstName,
            'practiceName'     => $notifiable->getPrimaryPracticeName(),
            'line2'            => $line2,
            'isSurveyOnly'     => false,
        ];
    }
}
