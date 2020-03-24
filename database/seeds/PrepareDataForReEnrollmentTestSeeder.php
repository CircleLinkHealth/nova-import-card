<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\Traits\Tests\UserHelpers;
use CircleLinkHealth\Customer\Entities\User;
use Faker\Factory;
use Illuminate\Database\Seeder;

class PrepareDataForReEnrollmentTestSeeder extends Seeder
{
    use UserHelpers;
    const CCM_STATUS_UNREACHABLE = 'unreachable';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        $faker     = Factory::create();
//        $mothStart = Carbon::parse(now())->copy()->startOfMonth()->toDateTimeString();
//        $monthEnd  = Carbon::parse($mothStart)->copy()->endOfMonth()->toDateTimeString();
//
//        $unreachablePatients = User::with('patientInfo')
//            ->whereDoesntHave('enrollmentInvitationLink')
//            ->whereHas('patientInfo', function ($patient) use ($mothStart, $monthEnd) {
//                $patient->where('ccm_status', self::CCM_STATUS_UNREACHABLE)->where([
//                    ['date_unreachable', '>=', $mothStart],
//                    ['date_unreachable', '<=', $monthEnd],
//                ]);
//            })->exists();
//
//        if ( ! $unreachablePatients) {
//            $n     = 1;
//            $limit = 5;
//            while ($n <= $limit) {
//                $user = $this->createUser(8, 'participant', self::CCM_STATUS_UNREACHABLE);
//                $user->patientInfo()->update([
//                    'birth_date' => $faker->date(),
//                ]);
//                ++$n;
//                $this->command->info("$n Patients");
//            }
//        }

//        $this->command->info('Finished but no patients needed to be create');
    }
}