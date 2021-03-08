<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Services\Postmark;

use CircleLinkHealth\Core\StringManipulation;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class InboundCallbackPhoneMatchService
{
    /**
     * @return Collection|User[]
     */
    public function getResults(string $phoneNumber, string $callerIdFieldPhone): Collection
    {
        $users = $this->matchUsers($phoneNumber, $callerIdFieldPhone)->get();
        if ($users->isNotEmpty()) {
            return $users;
        }

        $enrollees = $this->matchEnrollees($phoneNumber, $callerIdFieldPhone)->get();
        if ($enrollees->isNotEmpty()) {
            return $enrollees->map(function (Enrollee $e) {
                $fakeUser = new User();
                $fakeUser->id = 0;
                $fakeUser->setRelation('enrollee', $e);

                return $fakeUser;
            });
        }

        return collect();
    }

    private function matchEnrollees(string $phoneNumber, string $callerIdFieldPhone): Builder
    {
        $str1 = (new StringManipulation())->formatPhoneNumberE164($phoneNumber);
        $str2 = (new StringManipulation())->formatPhoneNumberE164($callerIdFieldPhone);
        $str  = ! empty($str2) ? implode(', ', [$str1, $str2]) : $str1;

        return Enrollee::searchPhones($str);
    }

    private function matchUsers(string $phoneNumber, string $callerIdFieldPhone): Builder
    {
        return User::ofTypePatients()
            ->with([
                'patientInfo' => function ($q) {
                    return $q->select(['id', 'ccm_status', 'user_id']);
                },
                'enrollee',
                'phoneNumbers' => function ($q) {
                    return $q->select(['id', 'user_id', 'number']);
                },
            ])
            ->searchPhoneNumber([$phoneNumber, $callerIdFieldPhone]);
    }
}
