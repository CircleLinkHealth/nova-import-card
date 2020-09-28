<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Core\Entities\SqlViewModel;
use CircleLinkHealth\Core\Filters\Filterable;

/**
 * App\CallView.
 *
 * @property int            $id
 * @property \Carbon\Carbon $call_time_start
 * @property \Carbon\Carbon $call_time_end
 * @property \Carbon\Carbon $patient_created_at
 * @property int|null no_call_attempts_since_last_success
 * @property int|null    $is_manual
 * @property string      $status
 * @property string|null $type
 * @property int|null    $nurse_id
 * @property string|null $nurse
 * @property int|null    $patient_id
 * @property string|null $patient
 * @property string|null $scheduled_date
 * @property string|null $last_call
 * @property int|null    $ccm_time
 * @property int|null    $bhi_time
 * @property int|null    $no_of_calls
 * @property int|null    $no_of_successful_calls
 * @property int|null    $practice_id
 * @property string|null $practice
 * @property string|null $state
 * @property string|null $timezone
 * @property string|null $preferred_call_days
 * @property int         $is_ccm
 * @property int         $is_bhi
 * @property string|null $scheduler
 * @property string|null $billing_provider
 * @property string|null $attempt_note
 * @property string|null $general_comment
 * @property string|null $ccm_status
 * @property string|null $patient_nurse_id
 * @property string|null $patient_nurse
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\CallView filter(\App\Filters\QueryFilters $filters)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\CallView newModelQuery()
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\CallView newQuery()
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\CallView query()
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\CallView whereAttemptNote($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\CallView whereBhiTime($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\CallView whereBillingProvider($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\CallView whereCallTimeEnd($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\CallView whereCallTimeStart($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\CallView whereCcmTime($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\CallView whereGeneralComment($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\CallView whereId($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\CallView whereIsBhi($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\CallView whereIsCcm($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\CallView whereIsManual($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\CallView whereLastCall($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\CallView whereNoOfCalls($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\CallView whereNoOfSuccessfulCalls($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\CallView whereNurse($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\CallView whereNurseId($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\CallView wherePatient($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\CallView wherePatientId($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\CallView wherePractice($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\CallView wherePracticeId($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\CallView wherePreferredCallDays($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\CallView whereScheduledDate($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\CallView whereScheduler($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\CallView whereStatus($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\CallView whereTimezone($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\CallView whereType($value)
 * @mixin \Eloquent
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CallView whereCcmStatus($value)
 * @property int                                                                                         $asap
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CallView whereAsap($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CallView wherePatientNurse($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CallView wherePatientNurseId($value)
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @property int|null                                                                                    $revision_history_count
 * @property string|null                                                                                 $preferred_contact_language
 * @property int|null                                                                                    $is_demo
 */
class CallView extends SqlViewModel
{
    use Filterable;

    public $phi = [
        'patient',
        'state',
    ];

    protected $table = 'calls_view';

    public function preferredCallDaysToString()
    {
        $days   = explode(',', $this->preferred_call_days);
        $result = [];
        foreach ($days as $day) {
            $result[] = $this->getDayFromInt($day);
        }

        return implode(',', $result);
    }

    private function getDayFromInt($dayInt)
    {
        switch ($dayInt) {
            case 1:
                return 'M';
            case 2:
                return 'Tu';
            case 3:
                return 'W';
            case 4:
                return 'Th';
            case 5:
                return 'F';
            case 6:
                return 'Sa';
            case 7:
                return 'Su';
            default:
                return '?';
        }
    }
}
