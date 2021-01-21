<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Database\Seeders;

use CircleLinkHealth\CcmBilling\Domain\Customer\SetupPracticeBillingData;
use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use CircleLinkHealth\SharedModels\Traits\Tests\PracticeLocation as PracticeLocationHelpers;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class NekatostrasClinicSeeder extends Seeder
{
    use PracticeLocationHelpers;
    use UserHelpers;
    const IATRO_SOPHIE_LOCATION = 'iatro_sophie';

    const NEKATOSTRAS_PRACTICE = 'nekatostras_clinic';

    public function run()
    {
        try {
            $practice = $this->firstOrCreatePractice(self::NEKATOSTRAS_PRACTICE);
            $location = $this->firstOrCreateLocation($practice->id, self::IATRO_SOPHIE_LOCATION);

            $practice->chargeableServices()->sync(ChargeableService::pluck('id')->toArray());

            foreach (array_merge(CpmConstants::PRACTICE_STAFF_ROLE_NAMES, [
                'care-center-external',
                'care-ambassador',
                'care-center',
                'administrator',
                'callbacks-admin',
                'software-only',
                'clh-ccm-admin',
            ]) as $roleName) {
                $u                       = $this->createUser($practice, $roleName);
                $u->first_name           = $roleName;
                $u->last_name            = 'User';
                $u->display_name         = "$u->first_name $u->last_name";
                $u->username             = $roleName;
                $u->auto_attach_programs = true;
                $u->password             = Hash::make('hello');
                $u->saas_account_id      = $practice->saas_account_id;
                $u->program_id           = $practice->id;
                $u->save();
                $u->locations()->sync([$location->id]);

                if ('provider' === $roleName) {
                    $this->createPatients($location, $u, 10);
                }
            }

            SetupPracticeBillingData::execute();
        } catch (ValidationException $e) {
            dd($e->validator->errors()->all());
        }
    }
}