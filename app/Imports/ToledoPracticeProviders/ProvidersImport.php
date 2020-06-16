<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Imports\ToledoPracticeProviders;

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ProvidersImport implements ToCollection
{
    const PUBLIC_PATH = '/img/signatures/Toledo Clinic';

    public function collection(Collection $rows)
    {
        /** @var Practice $practice */
        $practice  = Practice::where('display_name', 'Toledo Clinic')->first();
        $excelData = $this->excelProvidersData($rows);
        $users     = [];

        $signaturePicsPaths = $this->getSignaturesPicsPathDataFromPublic();
        foreach ($excelData  as $data) {
            /** @var User $user */
            $user = $this->getUserProvider($data, $practice);
            if ( ! is_null($user)) {
                if (empty($user->providerInfo->npi_number)) {
                    $user->providerInfo->update([
                        'npi_number' => $data['npi_number'],
                    ]);
                }
//                return;
            }
        }
    }

    /**
     * @return Collection
     */
    private function excelProvidersData(Collection $rows)
    {
        return $rows->map(function ($row) {
            return [
                'npi_number'      => $row[0],
                'email'           => $row[4],
                'location'        => $row[5],
                'npi_cross_check' => $row[6],
            ];
        });
    }

    private function getSignaturesPicsPathDataFromPublic()
    {
        $toledoSignatures = \File::allFiles(public_path(self::PUBLIC_PATH));

        $pathData = [];
        foreach ($toledoSignatures as $signaturePicPath) {
            $pathData[] = [
                'npiNumberFromPath' => substr($signaturePicPath, 0, strpos($signaturePicPath, '_')),
                'signaturePicPath'  => $signaturePicPath->getRelativePathname(),
            ];
        }

        return $pathData;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    private function getUserProvider(array $data, Practice $practice)
    {
        return User::// ofType('provider')->
        with('providerInfo')->where('program_id', $practice->id)
            ->where('email', $data['email'])
            ->whereHas('providerInfo')
            ->first();
    }
}
