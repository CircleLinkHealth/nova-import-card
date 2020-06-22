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

class UpdateProvidersFromExcel implements ToCollection, WithStartRow
{
    const PUBLIC_PATH = '/img/signatures/Toledo Clinic';

    public function collection(Collection $rows)
    {
        /** @var Practice $practice */
        $practice           = Practice::where('display_name', 'Toledo Clinic')->first();
        $excelProvidersData = $this->excelProvidersData($rows);
        
        $users=[];
        foreach ($excelProvidersData  as $data) {
            if (is_null($data['email'])) {
                throw new \Exception("Email is required for provider with npi_number {$data['npi_number']}");
            }/** @var User $user */
            $user = $this->getUserProvider($data['email'], $practice);
//            Feels weird that im doing the same check again.
            if ( ! is_null($user) && empty($user->providerInfo->npi_number)) {
//                Update npi number. Currently no toledo provider has any.
                    $user->providerInfo->update([
                        'npi_number' => $data['npi_number'],
                    ]);
//                    @todo: Update zip codes i locations
                $users[] = $user;
            }
        }

        $usersCount = count($users);
        Log::info("Npi_number has been updated for $usersCount enrollees");
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
    
}
