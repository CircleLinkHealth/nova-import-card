<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\Algorithms\Invoicing\AlternativeCareTimePayableCalculator;
use App\Call;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\CpmProblem;
use Illuminate\Database\Seeder;

class PatientMonthlySummarySeeder extends Seeder
{
    use UserHelpers;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $practice = Practice::whereName('demo')
            ->where('is_demo', true)
            ->first();

        $nurse = $this->createUser($practice->id, 'care-center');

        for ($i = 0; $i < 1000; ++$i) {
            /** @var User $user */
            $user = $this->createPatientWithProblems($practice->id);

            /** @var Call $call */
            $call = $this->createCallForPatient($nurse->id, $user->id);

            // @var PatientMonthlySummary $pms
            $this->createPatientMonthlySummaryForPatient($user->id);

            $patientProblems = $user->ccdProblems()->get();
            $call->attachAttestedProblems($patientProblems->pluck('id')->toArray());
        }
    }

    private function createCallForPatient($nurseUserId, $patientUserId)
    {
        return Call::create([
            'service'               => 'phone',
            'status'                => 'scheduled',
            'scheduler'             => 'core algorithm',
            'inbound_cpm_id'        => $patientUserId,
            'outbound_cpm_id'       => $nurseUserId,
            'inbound_phone_number'  => '',
            'outbound_phone_number' => '',
            'scheduled_date'        => Carbon::now()->toDateString(),
            'is_cpm_outbound'       => true,
        ]);
    }

    private function createPatientMonthlySummaryForPatient($patientId)
    {
        PatientMonthlySummary::updateOrCreate(
            [
                'month_year' => now()->startOfMonth()->toDate(),
                'patient_id' => $patientId,
            ],
            [
                'month_year'             => now()->startOfMonth()->toDate(),
                'total_time'             => AlternativeCareTimePayableCalculator::MONTHLY_TIME_TARGET_IN_SECONDS,
                'ccm_time'               => AlternativeCareTimePayableCalculator::MONTHLY_TIME_TARGET_IN_SECONDS,
                'bhi_time'               => 0,
                'no_of_calls'            => 10,
                'no_of_successful_calls' => 10,
                'patient_id'             => $patientId,
                'approved'               => 0,
                'rejected'               => 0,
                'needs_qa'               => 1,
                'actor_id'               => null,
            ]
        );
    }

    private function createPatientWithProblems($practiceId)
    {
        $user        = $this->createUser($practiceId, 'participant', 'enrolled');
        [$bhi, $ccm] = CpmProblem::get()->partition(function ($p) {
            return $p->is_behavioral;
        });

        $problemsForPatient = $bhi->take(2)->merge($ccm->take(8));

        foreach ($problemsForPatient as $problem) {
            $user->ccdProblems()->create([
                'name'           => $problem->name,
                'cpm_problem_id' => $problem->id,
            ]);
        }

        return $user;
    }
}
