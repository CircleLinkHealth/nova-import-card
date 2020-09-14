<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Database\Seeders;

use CircleLinkHealth\Customer\Entities\ChargeableService;
use Illuminate\Database\Seeder;

class ChargeableServiceHumanFriendlyNamesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ChargeableService::get()
            ->each(function (ChargeableService $cs) {
                $readbleName = $this->getNameUsingCode($cs->code);

                if ($readbleName) {
                    $cs->display_name = $readbleName;
                    $cs->save();
                }
            });
    }

    private function codeReadableNameMap(): array
    {
        return [
            ChargeableService::CCM            => 'CCM',
            ChargeableService::CCM_PLUS_40    => 'CCM40',
            ChargeableService::CCM_PLUS_60    => 'CCM60',
            ChargeableService::BHI            => 'BHI',
            ChargeableService::PCM            => 'PCM',
            ChargeableService::AWV_INITIAL    => 'AWV1',
            ChargeableService::AWV_SUBSEQUENT => 'AWV2',
            ChargeableService::G0511          => 'RHC',
        ];
    }

    private function getNameUsingCode(string $code): ?string
    {
        return $this->codeReadableNameMap()[$code] ?? null;
    }
}
