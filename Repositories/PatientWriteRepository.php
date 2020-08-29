<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Repositories;

use Carbon\Carbon;
use CircleLinkHealth\Core\Exceptions\InvalidArgumentException;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Collection;

class PatientWriteRepository
{
    /**
     * Mark the patient as Unreachable after this number of consecutive unsuccessful call attempts.
     */
    const MARK_UNREACHABLE_AFTER_FAILED_ATTEMPTS = 5;
    /**
     * The number of times to try calling a patient who was Unreachable, became enrolled by requesting a callback, before turning them back to unreachable.
     */
    const MAX_CALLBACK_ATTEMPTS = 2;

    /**
     * Set a patient's ccm_status to paused.
     *
     * @param $user
     *
     * @return bool
     */
    public function pause($user)
    {
        if (is_a($user, User::class)) {
            $userId = $user->id;
        }

        if (is_numeric($user)) {
            $userId = $user;
        }

        if ( ! isset($userId)) {
            throw new InvalidArgumentException();
        }

        return Patient::where('user_id', $userId)->update(['ccm_status' => 'paused']);
    }

    public function setStatus($userId, $status)
    {
        $stati = new Collection([Patient::PAUSED, Patient::ENROLLED, Patient::WITHDRAWN]);
        $user  = User::find($userId);
        if ($stati->contains($status) && $user) {
            Patient::where(['user_id' => $userId])->update(['ccm_status' => $status]);
        }

        return Patient::where(['user_id' => $userId])->first();
    }

    public function storeCcdProblem(User $patient, array $args)
    {
        if ( ! $args['code']) {
            return;
        }

        $newProblem = $patient->ccdProblems()->updateOrCreate([
            'name'           => $args['name'],
            'cpm_problem_id' => empty($args['cpm_problem_id'])
                ? null
                : $args['cpm_problem_id'],
            'billable'     => $args['billable'] ?? null,
            'is_monitored' => $args['is_monitored'] ?? false,
        ]);

        if ($args['code']) {
            $codeSystemId = getProblemCodeSystemCPMId([$args['code_system_name'], $args['code_system_oid']]);

            $code = $newProblem->codes()->create([
                'code_system_name'       => $args['code_system_name'],
                'code_system_oid'        => $args['code_system_oid'],
                'code'                   => $args['code'],
                'problem_code_system_id' => $codeSystemId,
            ]);
        }

        return $newProblem;
    }

    /**
     * Updates the patient's call info based on the status of the last call.
     *
     * @return \Illuminate\Database\Eloquent\Model|PatientMonthlySummary|static|null
     */
    public function updateCallLogs(
        Patient $patient,
        bool $successfulLastCall,
        bool $isCallBack = false,
        Carbon $forDate = null
    ) {
        if ( ! $forDate) {
            $forDate = Carbon::now();
        }

        $day_start = $forDate->firstOfMonth()->format('Y-m-d');
        $record    = PatientMonthlySummary::where('patient_id', $patient->user_id)
            ->where('month_year', $day_start)
            ->first();

        $successful_call_increment = 0;
        if ($successfulLastCall) {
            $successful_call_increment                    = 1;
            $patient->no_call_attempts_since_last_success = 0;
        } elseif ( ! $isCallBack) {
            $patient->no_call_attempts_since_last_success = ($patient->no_call_attempts_since_last_success + 1);
        }

        if ( ! $record) {
            $record                         = new PatientMonthlySummary();
            $record->patient_id             = $patient->user_id;
            $record->ccm_time               = 0;
            $record->month_year             = $day_start;
            $record->no_of_calls            = 1;
            $record->no_of_successful_calls = $successful_call_increment;
            $record->save();
        } else {
            $record->no_of_calls            = $record->no_of_calls + 1;
            $record->no_of_successful_calls = ($record->no_of_successful_calls + $successful_call_increment);
            $record->save();
        }

        if ( ! $successfulLastCall && ! $isCallBack && $patient->no_call_attempts_since_last_success >= self::MARK_UNREACHABLE_AFTER_FAILED_ATTEMPTS) {
            $patient->ccm_status                          = Patient::UNREACHABLE;
            $patient->no_call_attempts_since_last_success = 0;
        }

        if ($patient->isDirty()) {
            $patient->save();
        }

        return $record;
    }

    /**
     * @return bool
     */
    public function updatePausedLetterPrintedDate(array $userIdsToPrint, Carbon $dateTime = null)
    {
        if ( ! $dateTime) {
            $dateTime = Carbon::now();
        }

        return Patient::whereIn('user_id', $userIdsToPrint)
            ->update([
                'paused_letter_printed_at' => $dateTime->toDateTimeString(),
            ]);
    }
}
