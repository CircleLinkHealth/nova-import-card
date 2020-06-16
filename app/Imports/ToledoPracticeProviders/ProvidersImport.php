<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Imports\ToledoPracticeProviders;

use App\ProviderSignature;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
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

        $signaturePicsPaths = $this->getSignaturesPicsPathData();
        foreach ($excelData  as $data) {
            /** @var User $user */
            $user = $this->getUserProvider($data, $practice);
            if ( ! is_null($user)) {
                $users[] = $user;
                $this->updateProviderNpiNumber($user, $data['npi_number']);
                foreach ($signaturePicsPaths as $path) {
                    if ($path['npiNumberFromPath'] === $user->npi_number) {
                        $userId      = $user->id;
                        $type        = ProviderSignature::SIGNATURE_PIC_TYPE;
                        $publicPath  = public_path(self::PUBLIC_PATH);
                        $newPathName = "$userId$type";

                        return \File::move("$publicPath/$path", "$publicPath/$newPathName");
                    }
                }
            } else {
                Log::critical('ssdfsdfs');
            }
        }

//        $user = $users->each(function ($user) use ($npiNumberFromPath) {
//            return $user->providerInfo->where('npi_number', $npiNumberFromPath);
//        })->first();
//
//        if ( ! is_null($user)) {
//            $userId      = $user->id;
//            $type        = ProviderSignature::SIGNATURE_PIC_TYPE;
//            $publicPath  = public_path(self::PUBLIC_PATH);
//            $newPathName = "$userId$type";
//            \File::move("$publicPath/$signaturePicPath", "$publicPath/$newPathName");
//        }

        $x = 1;
    }

    /**
     * @return Collection
     */
    private function excelProvidersData(Collection $rows)
    {
        return $rows->map(function ($row) {
            return [
                'email'      => $row[3],
                'location'   => $row[4],
                'npi_number' => $row[5],
            ];
        });
    }

    private function getSignaturesPicsPathData()
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

    private function updateProviderNpiNumber(User $user, $npiNumber)
    {
        $user->providerInfo->update([
            'npi_number' => $npiNumber,
        ]);
    }
}
