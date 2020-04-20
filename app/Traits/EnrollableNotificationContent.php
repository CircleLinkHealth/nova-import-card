<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

trait EnrollableNotificationContent
{
    /**
     * @param $notifiable
     * @param $isReminder
     *
     * @return array|string
     */
    public function emailAndSmsContent($notifiable, $isReminder)
    {
        if ($isReminder) {
            if (!$this->hasSurveyCompleted($notifiable)) {
                $enrollableEmailContent = $this->getEmailContent($notifiable, $isReminder);
                $providerName = $enrollableEmailContent['providerName'];
                $practiceName = $enrollableEmailContent['practiceName'];
                $line2 = $enrollableEmailContent['line2'];
                $isSurveyOnly = $enrollableEmailContent['isSurveyOnly'];
            } else {
//                 If enrollables didnt take any action on invitation email.
                $enrollableEmailContent = $this->getEmailContent($notifiable, $isReminder);
                $providerName = $enrollableEmailContent['providerName'];
                $practiceName = $enrollableEmailContent['practiceName'];
                $line2 = $enrollableEmailContent['line2'];
                $isSurveyOnly = $enrollableEmailContent['isSurveyOnly'];
            }
        }

        if (!$isReminder) {
            $enrollableEmailContent = $this->getEmailContent($notifiable, $isReminder);
            $providerName = $enrollableEmailContent['providerName'];
            $practiceName = $enrollableEmailContent['practiceName'];
            $line2 = $enrollableEmailContent['line2'];
            $isSurveyOnly = $enrollableEmailContent['isSurveyOnly'];
        }

        $line1 = "Hi, it's $providerName's office at $practiceName!";
        $urlData = [
            'enrollable_id' => $notifiable->id,
            'is_survey_only' => $isSurveyOnly,
        ];

        return [
            'line1' => $line1,
            'line2' => $line2,
            'urlData' => $urlData,
        ];
    }

    /**
     * @param $notifiable
     * @param bool $isReminder
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
     * @return array
     */
    public function getEnrolleeEmailContent(User $notifiable, $isReminder)
    {
        $enrollee = Enrollee::whereUserId($notifiable->id)->firstOrFail();

        $provider = $enrollee->provider;
        $providerLastName = $provider->last_name;
        $line2 = $isReminder
            ? "Just circling back on Dr. $providerLastName new Personalized Care program. Please enroll or get more info here:"
            : "Dr. $providerLastName has invested in a new wellness program for you. Please enroll or get more info here:";

        return [
            'providerName' => $provider->display_name,
            'providerLastName' => $providerLastName,
            'practiceName' => $enrollee->practice->name,
            'line2' => $line2,
            'isSurveyOnly' => true,
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
        $testingMode = App::environment(['review', 'local']);
        $provider = $notifiable->billingProviderUser();
        $lastNurseThatPerformedActivity = $notifiable->patientInfo->lastNurseThatPerformedActivity();
        $nurseFirstName = !empty($lastNurseThatPerformedActivity) ? $lastNurseThatPerformedActivity->user->display_name
            : '';

        // Should never be empty with production data.
        if (empty($nurseFirstName)) {
            $nurseFirstName = $testingMode ? 'Adriannou' : '';
        }

        $line2 = $isReminder
            ? "Just circling back because $nurseFirstName, our telephone nurse, was unable to reach you this month. Please re-start calls in this link:"
            : "$nurseFirstName, our nurse, was unable to reach you this month. Please re-start calls in this link:";

        // Should never be empty with production data.
        if (!empty($provider)) {
            $providerName = $provider->display_name;
        } else {
            $providerName = $testingMode ? 'Dr. Costaris' : "your doctor's provider";
            Log::error("User $notifiable->id has null billingProviderUser");
        }

        return [
            'providerName' => $providerName,
            'nurseFirstName' => $nurseFirstName,
            'practiceName' => $notifiable->primaryPractice->name,
            'line2' => $line2,
            'isSurveyOnly' => false,
        ];
    }
}
