<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Imports\ToledoPracticeProviders;

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
        $practice           = Practice::where('display_name', 'Toledo Clinic')->first();
        $excelProvidersData = $this->excelProvidersData($rows);

        $users = [];
        foreach ($excelProvidersData  as $data) {
            if (is_null($data['email'])) {
                throw new \Exception("Email is required for provider with npi_number {$data['npi_number']}");
                $user = $this->getUserProvider($data['email'], $practice);

                if ( ! is_null($user) && empty($user->providerInfo->npi_number)) {
                    $user->providerInfo->update([
                        'npi_number' => $data['npi_number'],
                    ]);
                    $users[] = $user;
                }
            }

            $usersCount = count($users);
            Log::info("Npi_number has been updated for $usersCount enrollees");
        }
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

        return $nullFiltered->filter()->all();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    private function getUserProvider(string $email, Practice $practice)
    {
        $user = User::
        with('providerInfo')->where('program_id', $practice->id)
            ->where('email', $email)
            ->whereHas('providerInfo')
            ->first();

        if (is_null($user)) {
            Log::critical("Provider with user email $email not found");
        }

        return $user;
    }
}
