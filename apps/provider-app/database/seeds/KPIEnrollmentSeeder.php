<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Seeder;

class KPIEnrollmentSeeder extends Seeder
{
    public function csvData(): array
    {
        return parseCsvToArray(storage_path('enroller_kpi.csv'));
    }

    public function determinePracticeId($customer)
    {
        $arr = [
            'Clinica Los Angeles' => 30,
            'Clinica LA'          => 30,
            'Rocky Mountain'      => 29,
            'Premier Vein'        => 110,
            'Premier City'        => 119,
            'Quest'               => 111,
            'Ferguson'            => 134,
            'CCN Urgent Care'     => 135,
            'CCN'                 => 135,
            'UPG'                 => 16,
            'Humana-UPG'          => 16,
            'Phoenix Heart'       => 139,
            'Rappahannock'        => 140,
            'Cavallaro'           => 26,
            'LGH'                 => 141,
            'Ottawa'              => 158,
            'Jingo'               => 162,
            'Sia'                 => 160,
            'Bartay'              => 149,
            'Afonja'              => 159,
            'Shareef'             => 161,
            'Yambo'               => 165,
            'Calvary'             => 166,
            'Edgewater'           => 167,
            'Shelton'             => 169,
            'Glendale'            => 160,
            'Performance Pain'    => 171,
            'Crosslinks'          => 178,
            'Wickenburg'          => 180,
            'Neret'               => 172,
            'River City'          => 119,
            'CareMedica'          => 120,
            'N/A'                 => null,
            '0'                   => null,
        ];

        return $arr[$customer];
    }

    /**
     * Run the database seeds.
     */
    public function run()
    {
        $data = collect($this->csvData());

        foreach ($data as $row) {
            $fullname = explode(' ', $row['caller']);

            $firstName = $fullname[0];
            if (count($fullname) > 1) {
                $lastName = $fullname[1];
            } else {
                $lastName = '';
            }

            $user = User::where('first_name', $firstName)
                ->where('last_name', $lastName)
                ->first();

            if ( ! $user) {
                $user = User::create([
                    'email'           => "$firstName$lastName@clh.com",
                    'password'        => 'password',
                    'display_name'    => "$firstName $lastName",
                    'first_name'      => $firstName,
                    'last_name'       => $lastName,
                    'username'        => "$firstName$lastName",
                    'status'          => 'Inactive',
                    'access_disabled' => 1,
                    'user_status'     => 1,
                ]);

                $role = Role::where('name', 'care-ambassador')->first();
                $role = $user->attachGlobalRole($role->id);

                $ambassador = $user->careAmbassador()->create([
                    'hourly_rate'    => $row['rate'],
                    'speaks_spanish' => 0,
                ]);
            } else {
                $ambassador = $user->careAmbassador;
            }

            //first record only
            if ('prior to 2/16' == $row['by_day']) {
                $day = Carbon::parse('2/15/2017');
            } else {
                $day = Carbon::parse($row['by_day']);
            }

            $practiceId = $this->determinePracticeId($row['customer']);

            if ( ! $practiceId) {
                return;
            }

            $log = $ambassador->logs()->create([
                'day'                  => $day->toDateString(),
                'no_enrolled'          => $row['enrolled'],
                'no_rejected'          => $row['declined'],
                'no_utc'               => 0,
                'total_calls'          => $row['calls'],
                'total_time_in_system' => $row['hours'],
                'practice_id'          => $practiceId,
            ]);
        }
    }
}
