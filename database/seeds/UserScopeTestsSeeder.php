<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\Traits\Tests\UserHelpers;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\SaasAccount;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use Illuminate\Database\Seeder;

class UserScopeTestsSeeder extends Seeder
{
    use UserHelpers;
    const LOCATION_1_NAME                           = 'Liopetri';
    const LOCATION_2_NAME                           = 'Trivilloura';
    const LOCATION_3_NAME                           = 'Germasogeia';
    const PRACTICE_NAME                             = 'tasoulla_clinic';
    const PROVIDER_WITH_LOCATION_2_SCOPE_FIRST_NAME = 'Martis';
    const PROVIDER_WITH_LOCATION_2_SCOPE_LAST_NAME  = 'Kourti';
    const PROVIDER_WITH_LOCATION_3_SCOPE_FIRST_NAME = 'Iordanis';
    const PROVIDER_WITH_LOCATION_3_SCOPE_LAST_NAME  = 'Pouros';
    const PROVIDER_WITH_PRACTICE_SCOPE_FIRST_NAME   = 'Soulla';
    const PROVIDER_WITH_PRACTICE_SCOPE_LAST_NAME    = 'Masoulla';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $practice = $this->practice();

        $location1 = $this->createLocation($practice->id, self::LOCATION_1_NAME);
        $location2 = $this->createLocation($practice->id, self::LOCATION_2_NAME);
        $location3 = $this->createLocation($practice->id, self::LOCATION_3_NAME);

        $prov1 = $this->createProvider(
            self::PROVIDER_WITH_LOCATION_2_SCOPE_FIRST_NAME,
            self::PROVIDER_WITH_LOCATION_2_SCOPE_LAST_NAME,
            User::SCOPE_LOCATION,
            $location2
        );

        $prov2 = $this->createProvider(
            self::PROVIDER_WITH_LOCATION_3_SCOPE_FIRST_NAME,
            self::PROVIDER_WITH_LOCATION_3_SCOPE_LAST_NAME,
            User::SCOPE_LOCATION,
            $location3
        );

        $prov3 = $this->createProvider(
            self::PROVIDER_WITH_PRACTICE_SCOPE_FIRST_NAME,
            self::PROVIDER_WITH_PRACTICE_SCOPE_LAST_NAME,
            User::SCOPE_PRACTICE,
            $location1,
            $location2,
            $location3
        );

        $this->createPatients($location1, $prov3, 1);
        $this->createPatients($location2, $prov3, 2);
        $this->createPatients($location3, $prov3, 3);
        $this->createPatients($location2, $prov1, 4);
        $this->createPatients($location3, $prov2, 5);
    }

    private function createLocation(int $practiceId, string $name)
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

    private function createProvider(string $firstName, string $lastName, string $scope, Location ...$locations)
    {
        $locations = collect($locations);

        User::whereFirstName($firstName)->whereLastName($lastName)->forceDelete();

        $provider               = $this->createUser($locations->first()->practice_id, 'provider');
        $provider->scope        = $scope;
        $provider->username     = $firstName;
        $provider->first_name   = $firstName;
        $provider->last_name    = $lastName;
        $provider->display_name = $firstName.' '.$lastName.' MD';
        $provider->password     = Hash::make('hello');
        $provider->save();

        $provider->locations()->sync($locations->pluck('id')->all());

        return $provider;
    }

    private function practice()
    {
        $practice = Practice::whereName(self::PRACTICE_NAME)->first();

        if ( ! is_null($practice)) {
            return $practice;
        }

        return factory(Practice::class)->create([
            'name'            => self::PRACTICE_NAME,
            'display_name'    => snakeToSentenceCase(self::PRACTICE_NAME),
            'saas_account_id' => SaasAccount::whereName('CircleLink Health')->first()->id,
        ]);
    }
}
