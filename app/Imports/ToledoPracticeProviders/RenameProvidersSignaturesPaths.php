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
use Maatwebsite\Excel\Concerns\WithStartRow;

class RenameProvidersSignaturesPaths implements ToCollection, WithStartRow
{
    const PUBLIC_PATH = '/img/signatures/Toledo Clinic';

    public function collection(Collection $rows)
    {
        /** @var Practice $practice */
        $practice           = Practice::where('display_name', 'Toledo Clinic')->first();
        $excelProvidersData = $this->excelProvidersData($rows);
        $users              = [];

        $signaturePicsPaths      = $this->getSignaturesPicsPathDataFromPublic();
        $signaturePicsPathsCount = count($signaturePicsPaths);

        foreach ($excelProvidersData  as $data) {
            if (is_null($data['email'])) {
                throw new \Exception("Email is required for provider with npi_number {$data['npi_number']}");
            }

            /** @var User $user */
            $user = $this->getUserProvider($data['email'], $practice);
//            Feels weird that im doing the same check again.
            if ( ! is_null($user)) {
//                Update npi number. Currently no toledo provider has any.
                if (empty($user->providerInfo->npi_number)) {
                    $user->providerInfo->update([
                        'npi_number' => $data['npi_number'],
                    ]);
                }
                $users[] = $user;
            }
        }

        $usersCount = count($users);
        Log::info("$usersCount providers found in cpm from $signaturePicsPathsCount that were listed in excel sheet.");

        //        No reason to rename except if we want to for any reason.
//        $this->renameSignaturesPath($signaturePicsPaths, $users);
    }

    public function startRow(): int
    {
        return 2;
    }

    /**
     * @return array
     */
    private function excelProvidersData(Collection $rows)
    {
        $nullFiltered = $rows->transform(function ($row) {
            if ((string) $row[6] !== (string) $row[0]) {
                throw new \Exception("Npi number check failed in excel sheet for provider with email [$row[4]]");
            }

            return array_filter([
                'npi_number'      => $row[0],
                'email'           => $row[4],
                'location'        => $row[5],
                'npi_cross_check' => $row[6],
            ]);
        });
        // Filter again to remove empty arrays
        return $nullFiltered->filter()->all();
    }

    private function getSignaturesPicsPathDataFromPublic()
    {
        $toledoSignatures = \File::allFiles(public_path(self::PUBLIC_PATH));

        $pathData = [];
        foreach ($toledoSignatures as $signaturePicPath) {
            $relativePath = $signaturePicPath->getRelativePathname();
            $pathData[]   = [
                'npiNumberFromPath' => substr($relativePath, 0, strpos($relativePath, '_')),
                'signaturePicPath'  => $relativePath,
            ];
        }

        return $pathData;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    private function getUserProvider(string $email, Practice $practice)
    {
        $user = User::// ofType('provider')->
        with('providerInfo')->where('program_id', $practice->id)
            ->where('email', $email)
            ->whereHas('providerInfo')
            ->first();

        if (is_null($user)) {
//            Log message and continue code execution.
            Log::critical("Provider with user email $email not found");
        }

        return $user;
    }

    private function renameSignaturesPath(array $signaturePicsPaths, array $users)
    {
        foreach ($users as $user) {
            $npiNumber = $user->providerInfo->npi_number;
            if (is_null($npiNumber)) {
                Log::error("Provider with user_id [$user->id] has null npi_number");

                return;
            }
            if (in_array($npiNumber, collect($signaturePicsPaths)->flatten()->toArray())) {
                $userId      = $user->id;
                $type        = ProviderSignature::SIGNATURE_PIC_TYPE;
                $publicPath  = public_path(self::PUBLIC_PATH);
                $oldPathName = "$npiNumber$type";
                $newPathName = "$userId$type";
                \File::move("$publicPath/$oldPathName", "$publicPath/$newPathName");
                Log::info("$oldPathName renamed to $newPathName for provider with user_id [$user->id]");
            }
        }
    }
}
