<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits;

use CircleLinkHealth\Customer\Entities\User;
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
    private function emailAndSmsContent(User $notifiable, bool $isReminder)
    {
        $enrollableEmailContent = $this->getMessageContent($notifiable, $isReminder);
        $providerName           = $enrollableEmailContent['providerLastName'];
        $practiceName           = $enrollableEmailContent['practiceName'];
        $line2                  = $enrollableEmailContent['line2'];
        $isSurveyOnly           = $enrollableEmailContent['isSurveyOnly'];
        $providerSpecialty      = $enrollableEmailContent['providerSpecialty'];

        if (empty($practiceName)) {
            Log::warning("Practice name not found for user $notifiable->id");
            $practiceName = '???';
        }

        $line1 = "Hi, it's $providerSpecialty $providerName's office at $practiceName! ";

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
     * Should only pass User model which has enrollee -> user_id.
     *
     * @param $notifiable
     * @param $isReminder
     *
     * @throws \Exception
     *
     * @return array
     */
    private function getEnrolleeMessageContent(User $notifiable, bool $isReminder)
    {
        $provider = $notifiable->billingProviderUser();

        if (empty($provider)) {
            throw new \InvalidArgumentException("User[$notifiable->id] does not have a billing provider.");
        }

        $providerSpecialty = $this->providerSpecialty($provider);

        $providerLastName = ucwords($provider->last_name);

        $line2 = $isReminder
            ? "Just circling back on $providerSpecialty $providerLastName's new Personalized Care program. Please enroll or get more info here: "
            : "$providerSpecialty $providerLastName has invested in a new wellness program for you. Please enroll or get more info here: ";

        return [
            'providerLastName'  => $providerLastName,
            'practiceName'      => ucwords($notifiable->primaryPractice->display_name),
            'line2'             => $line2,
            'isSurveyOnly'      => true,
            'providerSpecialty' => $providerSpecialty,
        ];
    }

    /**
     * @throws \Exception
     *
     * @return array
     */
    private function getMessageContent(User $notifiable, bool $isReminder = false)
    {
        $notifiable->loadMissing([
            'billingProvider',
            'patientInfo',
            'primaryPractice' => function ($q) {
                return $q->select(['id', 'display_name', 'is_demo']);
            },
        ]);

        return $notifiable->isSurveyOnly()
            ? $this->getEnrolleeMessageContent($notifiable, $isReminder)
            : $this->getUnreachablePatientMessageContent($notifiable, $isReminder);
    }

    /**
     * @param $notifiable
     * @param $isReminder
     *
     * @return array
     */
    private function getUnreachablePatientMessageContent(User $notifiable, $isReminder)
    {
        $provider                       = $notifiable->billingProviderUser();
        $lastNurseThatPerformedActivity = $notifiable->patientInfo->lastNurseThatPerformedActivity();
        $nurseFirstName                 = ! empty($lastNurseThatPerformedActivity)
            ? ucwords($lastNurseThatPerformedActivity->user->display_name)
            : '';

        if ( ! empty($nurseFirstName)) {
            $line2 = $isReminder
                ? "Just circling back because $nurseFirstName, our telephone nurse was unable to reach you this month. Please re-start calls in this link: "
                : "$nurseFirstName, our nurse, was unable to reach you this month. Please re-start calls in this link: ";
        } else {
            $line2 = $isReminder
                ? 'Just circling back because our telephone nurse, was unable to reach you this month. Please re-start calls in this link: '
                : 'Your Nurse Care Coach was unable to reach you this month. Please re-start calls in this link: ';
        }

        if ( ! empty($provider)) {
            $providerLastName = $provider->last_name;
        } else {
            $providerLastName = '';
            Log::error("User $notifiable->id has null billingProviderUser");
        }

        return [
            'providerLastName' => ucwords($providerLastName),
            'nurseFirstName'   => $nurseFirstName,
            'practiceName'     => ucwords($notifiable->getPrimaryPracticeName()),
            'line2'            => $line2,
            'isSurveyOnly'     => false,
        ];
    }

    /**
     * @return string
     */
    private function providerSpecialty(User $provider)
    {
        $providerSpecialty = 'Dr.';
        if ($provider->isCareCoach()) {
            $providerSpecialty = 'Nurse Practitioner';
        }

        return $providerSpecialty;
    }
}
