<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Enrollment\PracticeSpecificLetter;

use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ToledoDemoLetter
{
    public function letterSpecifications(Model $practiceLetter, string $practiceDisplayName, User $userEnrollee)
    {
        $uiRequests       = json_decode($practiceLetter->ui_requests);
        $uiRequestsExists = ! is_null($uiRequests);
//        Toledo needs logo on the right.
        $logoStyleRequest = $uiRequestsExists ? $uiRequests->logo_position : '';
//        $logoStyleRequest = 'text-align:right';
//        Toledo needs some extra data in letter top left.
        $extraAddressHeader = $uiRequestsExists ? collect($uiRequests->extra_address_header) : collect();

        $extraAddressValues = collect()->first();
        if ( ! empty($extraAddressHeader)) {
            $models = $this->getModelsContainingNeededValues($extraAddressHeader);
            foreach ($models as $model => $props) {
                if ($practiceDisplayName === $model) {
                    $extraAddressValues[] = $this->getExtraAddressValues($props, $userEnrollee);
                }
//                Else use $model to query.
            }
        }

        return  [
            'extraAddressValuesRequested' => ! empty(collect($extraAddressValues)->filter()->all()),
            'logoStyleRequest'            => $logoStyleRequest,
            'extraAddressValues'          => $extraAddressValues,
        ];
    }

    /**
     * @return array
     */
    private function getExtraAddressValues(array $props, User $userEnrollee)
    {
        $practiceLocation      = $this->getPracticeLocation($userEnrollee);
        $practiceLocationArray = $practiceLocation->toArray();

        if (empty($practiceLocationArray)) {
            return [];
        }

        return collect($props[0])->mapWithKeys(function ($prop) use ($practiceLocationArray) {
            return  [
                $prop => $practiceLocationArray[$prop],
            ];
        })->toArray();
    }

    private function getModelsContainingNeededValues(\Illuminate\Support\Collection $extraAddressHeader)
    {
        return $extraAddressHeader->map(function ($model) {
            return [$model];
        });
    }

    /**
     * @return \App\Location|\Collection|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection|object
     */
    private function getPracticeLocation(User $userEnrollee)
    {
        $enrolleePracticeLocationId = $userEnrollee->enrollee->location_id;

        $practiceLocation = collect();
        if ( ! empty($enrolleePracticeLocationId)) {
            $practiceLocation = Location::whereId($enrolleePracticeLocationId)->first();
        }

        // We want keep code execution if no practice location exists.
        if (is_null($practiceLocation)) {
            Log::info("Location for practice [$userEnrollee->id] not found. No practice location address will be displayed on letter");

            return collect();
        }

        return $practiceLocation;
    }
}
