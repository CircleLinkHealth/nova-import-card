<?php

namespace App\Repositories;


use App\Exceptions\InvalidArgumentException;
use App\Patient;
use App\PatientMonthlySummary;
use App\User;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class PatientWriteRepository
{
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
            'billable'       => $args['billable'] ?? null,
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
     * Updates the patient's call info based on the status of the last call
     *
     * @param Patient $patient
     * @param $successfulLastCall
     *
     * @return PatientMonthlySummary|\Illuminate\Database\Eloquent\Model|null|static
     */
    public function updateCallLogs(
        Patient $patient,
        bool $successfulLastCall
    ) {

        // get record for month
        $day_start = Carbon::parse(Carbon::now()->firstOfMonth())->format('Y-m-d');
        $record = PatientMonthlySummary::where('patient_id', $patient->user_id)
                                       ->where('month_year', $day_start)
                                       ->first();

        // set increment var
        $successful_call_increment = 0;
        if ($successfulLastCall) {
            $successful_call_increment = 1;
            // reset call attempts back to 0
            $patient->no_call_attempts_since_last_success = 0;
        } else {
            $patient->no_call_attempts_since_last_success = ($patient->no_call_attempts_since_last_success + 1);

            if ($patient->no_call_attempts_since_last_success == 5 && optional($record)->no_of_successful_calls < 1) {
                $patient->ccm_status = 'paused';
            }
        }
        $patient->save();

        // Determine whether to add to record or not
        if (!$record) {
            $record = new PatientMonthlySummary;
            $record->patient_id = $patient->user_id;
            $record->ccm_time = 0;
            $record->month_year = $day_start;
            $record->no_of_calls = 1;
            $record->no_of_successful_calls = $successful_call_increment;
            $record->save();
        } else {
            $record->no_of_calls = $record->no_of_calls + 1;
            $record->no_of_successful_calls = ($record->no_of_successful_calls + $successful_call_increment);
            $record->save();
        }

        return $record;
    }

    /**
     * @param array $userIdsToPrint
     * @param Carbon|null $dateTime
     *
     * @return bool
     */
    public function updatePausedLetterPrintedDate(array $userIdsToPrint, Carbon $dateTime = null)
    {
        if (!$dateTime) {
            $dateTime = Carbon::now();
        }

        return Patient::whereIn('user_id', $userIdsToPrint)
            ->update([
                'paused_letter_printed_at' => $dateTime->toDateTimeString(),
            ]);
    }

    public function setStatus($userId, $status) {
        $stati = new Collection([ Patient::PAUSED, Patient::ENROLLED, Patient::WITHDRAWN ]);
        $user = User::find($userId);
        if ($stati->contains($status) && $user) {
            Patient::where([ 'user_id' => $userId ])->update([ 'ccm_status' => $status ]);
        }
        return Patient::where([ 'user_id' => $userId ])->first();
    }
}