<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Helpers\Users;

use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\SaasAccount;

trait PracticeLocation
{
    private function firstOrCreateLocation(int $practiceId, string $name)
    {
        $location = Location::whereName($name)->first();

        if ( ! is_null($location)) {
            return $location;
        }

        return factory(Location::class)->create([
            'name'        => $name,
            'practice_id' => $practiceId,
        ]);
    }

    private function firstOrCreatePractice(string $practiceName)
    {
        $practice = Practice::whereName($practiceName)->first();

        if ( ! is_null($practice)) {
            return $practice;
        }

        return factory(Practice::class)->create([
            'name'            => $practiceName,
            'display_name'    => snakeToSentenceCase($practiceName),
            'saas_account_id' => SaasAccount::whereName('CircleLink Health')->first()->id,
        ]);
    }
}
