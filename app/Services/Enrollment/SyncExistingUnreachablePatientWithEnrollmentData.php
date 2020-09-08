<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Enrollment;

use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\PatientContactWindow;
use CircleLinkHealth\Customer\Entities\PhoneNumber;
use CircleLinkHealth\Eligibility\Entities\Enrollee;

class SyncExistingUnreachablePatientWithEnrollmentData
{
    protected Enrollee $enrollee;

    public function __construct(Enrollee $enrollee)
    {
        $this->enrollee = $enrollee;
    }

    public static function execute(Enrollee $enrollee): void
    {
        (new static($enrollee))->update();
    }

    public function update(): void
    {
        $this->updateUserModel()
            ->updatePhones()
            ->updateContactWindows()
            ->updatePatientInfo();
    }

    private function updateContactWindows(): self
    {
        $preferredCallDays  = $this->enrollee->getPreferredCallDays();
        $preferredCallTimes = $this->enrollee->getPreferredCallTimes();

        $patientInfo = $this->enrollee->user->patientInfo;

        if ( ! $preferredCallDays && ! $preferredCallTimes) {
            PatientContactWindow::sync(
                $patientInfo,
                [
                    1,
                    2,
                    3,
                    4,
                    5,
                ]
            );
        } else {
            PatientContactWindow::sync(
                $patientInfo,
                $preferredCallDays,
                $preferredCallTimes['start'],
                $preferredCallTimes['end']
            );
        }

        return $this;
    }

    private function updatePatientInfo(): self
    {
        $patientInfo = $this->enrollee->user->patientInfo;

        if ('agent' === $this->enrollee->getPreferredPhoneType()) {
            $patientInfo->agent_email        = $this->enrollee->getAgentAttribute(Enrollee::AGENT_EMAIL_KEY);
            $patientInfo->agent_name         = $this->enrollee->getAgentAttribute(Enrollee::AGENT_NAME_KEY);
            $patientInfo->agent_relationship = $this->enrollee->getAgentAttribute(Enrollee::AGENT_RELATIONSHIP_KEY);
            $patientInfo->agent_telephone    = $this->enrollee->getAgentAttribute(Enrollee::AGENT_PHONE_KEY);
        }

        if ($this->enrollee->other_note) {
            $patientInfo->general_comment = $this->enrollee->other_note;
        }

        $patientInfo->ccm_status = Patient::ENROLLED;
        $patientInfo->save();

        return $this;
    }

    private function updatePhones(): self
    {
        $primaryPhonePhone = $this->enrollee->primary_phone_e164;
        $homePhone         = $this->enrollee->home_phone_e164;
        $mobilePhone       = $this->enrollee->cell_phone_e164;
        $workPhone         = null;

        $preferredPhoneType = $this->enrollee->getPreferredPhoneType();
        //if agent is preferred phone, this will show on patient profile.

        if ('other' == $preferredPhoneType) {
            $otherPhone = $this->enrollee->other_phone_e164;
            //if preferred phone is other phone, then check if home or mobile is empty, so we can save it as that. Else Save it as work for the moment.
            //todo:investigate better solutions
            if ( ! $homePhone) {
                $homePhone          = $otherPhone;
                $preferredPhoneType = 'home';
            } elseif ( ! $mobilePhone) {
                $mobilePhone        = $otherPhone;
                $preferredPhoneType = 'cell';
            } else {
                $workPhone          = $otherPhone;
                $preferredPhoneType = 'work';
            }
        }

        if ($homePhone) {
            PhoneNumber::updateOrCreate(
                [
                    'user_id' => $userId = $this->enrollee->user_id,
                    'number'  => $homePhone,
                    'type'    => PhoneNumber::HOME,
                ],
                [
                    'is_primary' => 'home' === $preferredPhoneType,
                ]
            );
        }

        if ($mobilePhone) {
            PhoneNumber::updateOrCreate(
                [
                    'user_id' => $userId,
                    'number'  => $mobilePhone,
                    'type'    => PhoneNumber::MOBILE,
                ],
                [
                    'is_primary' => 'cell' === $preferredPhoneType,
                ]
            );
        }

        if ($workPhone) {
            PhoneNumber::updateOrCreate(
                [
                    'user_id' => $userId,
                    'number'  => $workPhone,
                    'type'    => PhoneNumber::WORK,
                ],
                [
                    'is_primary' => 'work' === $preferredPhoneType,
                ]
            );
        }

        return $this;
    }

    private function updateUserModel(): self
    {
        $patientUser = $this->enrollee->user;

        $patientUser->address  = $this->enrollee->address;
        $patientUser->address2 = $this->enrollee->address_2;
        $patientUser->city     = $this->enrollee->city;
        $patientUser->state    = $this->enrollee->state;
        $patientUser->zip      = $this->enrollee->zip;
        $patientUser->email    = $this->enrollee->email;

        $patientUser->save();

        return $this;
    }
}
