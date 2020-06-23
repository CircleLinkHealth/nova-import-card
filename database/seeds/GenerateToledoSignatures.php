<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\ProviderSignature;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Seeder;

class GenerateToledoSignatures extends Seeder
{
    const TOLEDO_CLINIC = 'toledo-clinic';
    const TOLEDO_DEMO   = 'toledo-demo';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $npiNumbers = [
            '1477587517',
            '1770584088',
            '1639179963',
            '1598798589',
            '1770547259',
            '1093710410',
            '1164407532',
            '1801880471',
            '1588665145',
            '1750386173',
            '1316946098',
            '1316947591',
            '1629068499',
            '1366419830',
            '1063405033',
            '1891771184',
            '1801863386',
            '1831399484',
            '1881679140',
            '1245237007',
            '1265432389',
            '1184687014',
            '1235254608',
            '1851344402',
            '1073513198',
            '1932107562',
            '1902124811',
            '1679570352',
            '1104821503',
            '1245218312',
            '1255332359',
            '1215913546',
            '1386644938',
            '1932104072', // This one does not have a signature
            '1588664833',
            '1467437483',
            '1891950580',
            '1407848062',
            '1528050226',
            '1720086986',
            '1710982145',
            '1881695492',
            '1962409979',
        ];

        $practiceName = \Illuminate\Support\Facades\App::environment(['testing, staging, review']) ? self::TOLEDO_DEMO : self::TOLEDO_CLINIC;
        $practiceId   = $this->getPractice($practiceName);

        User::ofType('provider')
            ->with('providerInfo.signature')
            ->where('program_id', $practiceId)
            ->whereHas('providerInfo', function ($provider) {
                $provider->whereNotNull('npi_number');
            })->chunk(20, function ($users) use ($npiNumbers) {
                foreach ($npiNumbers as $npiNumber) {
                    foreach ($users as $user) {
                        if ($user->providerInfo->npi_number === $npiNumber) {
                            $providerNpiNumber = $user->providerInfo->npi_number;
                            $type = ProviderSignature::SIGNATURE_PIC_TYPE;

                            $user->providerInfo->signature()->updateOrCreate(
                                [
                                    'signature_src' => "/img/signatures/Toledo/$providerNpiNumber$type",
                                ]
                            );
                        }
                    }
                }
            });
    }

    private function getPractice($practiceName)
    {
        $toledoPractice = Practice::where('name', '=', $practiceName)->first();

        if ( ! $toledoPractice) {
            throw new Exception("$practiceName Practice not found in Practices");
        }

        return $toledoPractice->id;
    }
}
