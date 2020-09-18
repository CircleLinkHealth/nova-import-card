<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Core\Entities\SqlViewModel;

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
 * @method   static      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CallView filter(\CircleLinkHealth\Core\Filters\QueryFilters $filters)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CallView newModelQuery()
 * @method   static      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CallView newQuery()
 * @method   static      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CallView query()
 * @method   static      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CallView whereAttemptNote($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CallView whereBhiTime($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CallView whereBillingProvider($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CallView whereCallTimeEnd($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CallView whereCallTimeStart($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CallView whereCcmTime($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CallView whereGeneralComment($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CallView whereId($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CallView whereIsBhi($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CallView whereIsCcm($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CallView whereIsManual($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CallView whereLastCall($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CallView whereNoOfCalls($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CallView whereNoOfSuccessfulCalls($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CallView whereNurse($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CallView whereNurseId($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CallView wherePatient($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CallView wherePatientId($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CallView wherePractice($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CallView wherePracticeId($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CallView wherePreferredCallDays($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CallView whereScheduledDate($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CallView whereScheduler($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CallView whereStatus($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CallView whereTimezone($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CallView whereType($value)
 * @mixin \Eloquent
 * @method   static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CallView whereCcmStatus($value)
 * @property int    $asap
 * @method   static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CallView whereAsap($value)
 * @method   static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CallView wherePatientNurse($value)
 * @method   static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CallView wherePatientNurseId($value)
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection
 *     $revisionHistory
 * @property int|null                        $revision_history_count
 * @property int                             $ccda_id
 * @property int|null                        $patient_user_id
 * @property int|null                        $enrollee_id
 * @property int|null                        $location_id
 * @property int|null                        $billing_provider_id
 * @property string                          $source
 * @property mixed|null                      $validation_errors
 * @property int|null                        $nurse_user_id
 * @property string|null                     $nurse_user_name
 * @property string|null                     $practice_display_name
 * @property string|null                     $practice_name
 * @property int|null                        $dm_id
 * @property string|null                     $dm_from
 * @property string|null                     $mrn
 * @property string|null                     $patient_first_name
 * @property string|null                     $enrollee_first_name
 * @property string|null                     $first_name
 * @property string|null                     $patient_last_name
 * @property string|null                     $enrolleet_last_name
 * @property string|null                     $last_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\CcdaView whereBillingProviderId($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\CcdaView whereCcdaId($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\CcdaView whereCreatedAt($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\CcdaView whereDmFrom($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\CcdaView whereDmId($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\CcdaView whereEnrolleeFirstName($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\CcdaView whereEnrolleeId($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\CcdaView whereEnrolleetLastName($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\CcdaView whereFirstName($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\CcdaView whereLastName($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\CcdaView whereLocationId($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\CcdaView whereMrn($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\CcdaView whereNurseUserId($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\CcdaView whereNurseUserName($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\CcdaView wherePatientFirstName($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\CcdaView wherePatientLastName($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\CcdaView wherePatientUserId($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\CcdaView wherePracticeDisplayName($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\CcdaView wherePracticeName($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\CcdaView whereSource($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\CcdaView whereValidationErrors($value)
 * @property string|null                     $provider_name
 * @property string|null                     $dob
 * @property string|null                     $enrollee_last_name
 */
class CcdaView extends SqlViewModel
{
    public $phi = [
        'dob',
        'mrn',
        'patient_first_name',
        'enrollee_first_name',
        'first_name',
        'patient_last_name',
        'enrollee_last_name',
        'last_name',
        'provider_name',
    ];
    protected $dates      = ['dob'];
    protected $primaryKey = 'ccda_id';

    protected $table = 'ccdas_view';
}
