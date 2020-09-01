<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Helpers\Users;

use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\SaasAccount;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\CarePlan;

trait PracticeLocation
{
    private function createPatients(Location $location, User $provider, int $count)
    {
        $patients = collect();

        for ($i = 1; $i <= $count; ++$i) {
            $patients->push($patient = $this->createUser($location->practice_id, 'participant'));
            $patient->locations()->sync([$location->id]);

            CarePlan::where('user_id', $patient->id)
                ->update([
                    'status' => CarePlan::RN_APPROVED,
                ]);

            $patient->setBillingProviderId($provider->id);
        }

        Patient::whereIn('user_id', $patients->pluck('id')->all())->update([
            'preferred_contact_location' => $location->id,
        ]);

        return $patients;
    }

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
