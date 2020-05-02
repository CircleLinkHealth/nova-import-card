<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\Traits\Tests\UserHelpers;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\CcdaImporter\Traits\SeedEligibilityJobsForEnrollees;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Database\Seeder;

class PrepareDataForReEnrollmentTestSeeder extends Seeder
{
    use SeedEligibilityJobsForEnrollees;
    use UserHelpers;

    const CCM_STATUS_UNREACHABLE = 'unreachable';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $practice = Practice::firstOrCreate(
            [
                'name' => 'demo',
            ],
            [
                'active'                => 1,
                'display_name'          => 'Demo',
                'is_demo'               => 1,
                'clh_pppm'              => 0,
                'term_days'             => 30,
                'outgoing_phone_number' => 2025550196,
            ]
        );
        $mothStart = Carbon::parse(now())->copy()->startOfMonth()->toDateTimeString();
        $monthEnd  = Carbon::parse($mothStart)->copy()->endOfMonth()->toDateTimeString();

        $enrollees = Enrollee::where('dob', \Carbon\Carbon::parse('1901-01-01'))
            ->where('practice_id', $practice->id)
            ->whereDoesntHave('enrollmentInvitationLink');

        if ($enrollees->count() < 5) {
            $enrollees->delete(); //Just to be sure
            $n     = 1;
            $limit = 5;
            while ($n <= $limit) {
                $enrolleesForTesting = factory(Enrollee::class, 1)->create([
                    'practice_id'             => $practice->id,
                    'dob'                     => \Carbon\Carbon::parse('1901-01-01'),
                    'referring_provider_name' => 'Dr. Demo',
                    'mrn'                     => mt_rand(100000, 999999),
                    'primary_phone'           => '8759355561',
                ]);
                $this->seedEligibilityJobs(collect($enrolleesForTesting));
                ++$n;
            }
        }

        $unreachablePatients = User::with('patientInfo')
            ->where('program_id', $practice->id)
            ->whereDoesntHave('enrollmentInvitationLink')
            ->whereHas('patientInfo', function ($patient) use ($mothStart, $monthEnd) {
                // @var Patient $patient
                $patient->where('ccm_status', self::CCM_STATUS_UNREACHABLE)->where([
                    ['date_unreachable', '>=', $mothStart],
                    ['date_unreachable', '<=', $monthEnd],
                ])->where('birth_date', '=', '1901-01-01');
            });

        if ($unreachablePatients->count() < 5) {
            $unreachablePatients->forceDelete(); //Just to be sure
            $n     = 1;
            $limit = 5;
            while ($n <= $limit) {
                $user = $this->createUser($practice->id, 'participant', self::CCM_STATUS_UNREACHABLE);
                $user->patientInfo()->update([
                    'birth_date'       => \Carbon\Carbon::parse('1901-01-01'),
                    'date_unreachable' => now(),
                ]);
                ++$n;
            }
        }
    }
}
