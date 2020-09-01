<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\Traits\Tests\UserHelpers;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use Illuminate\Database\Seeder;
use Tests\Helpers\Users\PracticeLocation as PracticeLocationHelpers;

class UserScopeTestsSeeder extends Seeder
{
    use PracticeLocationHelpers;
    use UserHelpers;
    const LOCATION_1_NAME                                       = 'Liopetri';
    const LOCATION_2_NAME                                       = 'Trivilloura';
    const LOCATION_3_NAME                                       = 'Germasogeia';
    const MED_ASS_WITH_MULTIPLE_LOCATIONS_SCOPE_FIRST_NAME      = 'Panayiotis';
    const MED_ASS_WITH_MULTIPLE_LOCATIONS_SCOPE_LAST_NAME       = 'Cornos';
    const MED_ASS_WITH_ONE_LOCATION_SCOPE_FIRST_NAME            = 'Pantelitsa';
    const MED_ASS_WITH_ONE_LOCATION_SCOPE_LAST_NAME             = 'Hadjidiamanti';
    const OFFICE_ADMIN_WITH_MULTIPLE_LOCATIONS_SCOPE_FIRST_NAME = 'Markos';
    const OFFICE_ADMIN_WITH_MULTIPLE_LOCATIONS_SCOPE_LAST_NAME  = 'Poutsis';
    const OFFICE_ADMIN_WITH_ONE_LOCATION_SCOPE_FIRST_NAME       = 'Tanjelou';
    const OFFICE_ADMIN_WITH_ONE_LOCATION_SCOPE_LAST_NAME        = 'Capillé Du Liban';
    const PRACTICE_NAME                                         = 'tasoulla_clinic';
    const PROVIDER_WITH_LOCATION_2_SCOPE_FIRST_NAME             = 'Martis';
    const PROVIDER_WITH_LOCATION_2_SCOPE_LAST_NAME              = 'Kourti';
    const PROVIDER_WITH_LOCATION_3_SCOPE_FIRST_NAME             = 'Iordanis';
    const PROVIDER_WITH_LOCATION_3_SCOPE_LAST_NAME              = 'Pouros';
    const PROVIDER_WITH_MULTIPLE_LOCATIONS_SCOPE_FIRST_NAME     = 'Markella';
    const PROVIDER_WITH_MULTIPLE_LOCATIONS_SCOPE_LAST_NAME      = 'Istrefi';
    const PROVIDER_WITH_PRACTICE_SCOPE_FIRST_NAME               = 'Soulla';
    const PROVIDER_WITH_PRACTICE_SCOPE_LAST_NAME                = 'Masoulla';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $practice = $this->firstOrCreatePractice(self::PRACTICE_NAME);

        $location1 = $this->firstOrCreateLocation($practice->id, self::LOCATION_1_NAME);
        $location2 = $this->firstOrCreateLocation($practice->id, self::LOCATION_2_NAME);
        $location3 = $this->firstOrCreateLocation($practice->id, self::LOCATION_3_NAME);

        $prov1 = $this->createStaffMember(
            'provider',
            self::PROVIDER_WITH_LOCATION_2_SCOPE_FIRST_NAME,
            self::PROVIDER_WITH_LOCATION_2_SCOPE_LAST_NAME,
            User::SCOPE_LOCATION,
            $location2
        );
        $prov2 = $this->createStaffMember(
            'provider',
            self::PROVIDER_WITH_LOCATION_3_SCOPE_FIRST_NAME,
            self::PROVIDER_WITH_LOCATION_3_SCOPE_LAST_NAME,
            User::SCOPE_LOCATION,
            $location3
        );
        $prov3 = $this->createStaffMember(
            'provider',
            self::PROVIDER_WITH_PRACTICE_SCOPE_FIRST_NAME,
            self::PROVIDER_WITH_PRACTICE_SCOPE_LAST_NAME,
            User::SCOPE_PRACTICE,
            $location1,
            $location2,
            $location3
        );
        $prov4 = $this->createStaffMember(
            'provider',
            self::PROVIDER_WITH_MULTIPLE_LOCATIONS_SCOPE_FIRST_NAME,
            self::PROVIDER_WITH_MULTIPLE_LOCATIONS_SCOPE_LAST_NAME,
            User::SCOPE_LOCATION,
            $location2,
            $location3
        );

        $this->createPatients($location1, $prov3, 8);
        $this->createPatients($location2, $prov3, 2);
        $this->createPatients($location3, $prov3, 3);
        $this->createPatients($location2, $prov1, 4);
        $this->createPatients($location3, $prov2, 5);
        $this->createPatients($location3, $prov4, 6);
        $this->createPatients($location2, $prov4, 7);

        $medAss1 = $this->createStaffMember(
            'med_assistant',
            self::MED_ASS_WITH_MULTIPLE_LOCATIONS_SCOPE_FIRST_NAME,
            self::MED_ASS_WITH_MULTIPLE_LOCATIONS_SCOPE_LAST_NAME,
            User::SCOPE_LOCATION,
            $location2,
            $location3
        );
        $medAss2 = $this->createStaffMember(
            'med_assistant',
            self::MED_ASS_WITH_ONE_LOCATION_SCOPE_FIRST_NAME,
            self::MED_ASS_WITH_ONE_LOCATION_SCOPE_LAST_NAME,
            User::SCOPE_LOCATION,
            $location2
        );

        $officeAdmin1 = $this->createStaffMember(
            'office_admin',
            self::OFFICE_ADMIN_WITH_MULTIPLE_LOCATIONS_SCOPE_FIRST_NAME,
            self::OFFICE_ADMIN_WITH_MULTIPLE_LOCATIONS_SCOPE_LAST_NAME,
            User::SCOPE_LOCATION,
            $location2,
            $location3
        );
        $officeAdmin2 = $this->createStaffMember(
            'office_admin',
            self::OFFICE_ADMIN_WITH_ONE_LOCATION_SCOPE_FIRST_NAME,
            self::OFFICE_ADMIN_WITH_ONE_LOCATION_SCOPE_LAST_NAME,
            User::SCOPE_LOCATION,
            $location1
        );
    }

    private function createStaffMember(string $roleName, string $firstName, string $lastName, string $scope, Location ...$locations)
    {
        $locations = collect($locations);

        User::whereFirstName($firstName)->whereLastName($lastName)->forceDelete();

        $provider               = $this->createUser($locations->first()->practice_id, $roleName);
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
}
