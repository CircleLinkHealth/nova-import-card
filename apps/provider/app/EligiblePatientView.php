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
 * @property int|null    $revision_history_count
 * @property int         $eligibility_job_id
 * @property string|null $provider
 * @property string|null $primary_insurance
 * @property string|null $secondary_insurance
 * @property string|null $tertiary_insurance
 * @property string|null $last_encounter
 * @property string|null $home_phone
 * @property string|null $cell_phone
 * @property string|null $dob
 * @property string|null $lang
 * @property string|null $mrn
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $address
 * @property string|null $address_2
 * @property string|null $city
 * @property string|null $state
 * @property string|null $zip
 * @property string|null $primary_phone
 * @property string|null $other_phone
 * @property string|null $email
 * @property string|null $medical_record_type
 * @property string|null $medical_record_id
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\EligiblePatientView whereAddress($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\EligiblePatientView whereAddress2($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\EligiblePatientView whereCellPhone($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\EligiblePatientView whereCity($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\EligiblePatientView whereDob($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\EligiblePatientView whereEligibilityJobId($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\EligiblePatientView whereEmail($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\EligiblePatientView whereFirstName($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\EligiblePatientView whereHomePhone($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\EligiblePatientView whereLang($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\EligiblePatientView whereLastEncounter($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\EligiblePatientView whereLastName($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\EligiblePatientView whereMedicalRecordId($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\EligiblePatientView whereMedicalRecordType($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\EligiblePatientView whereMrn($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\EligiblePatientView whereOtherPhone($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\EligiblePatientView wherePrimaryInsurance($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\EligiblePatientView wherePrimaryPhone($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\EligiblePatientView whereProvider($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\EligiblePatientView whereSecondaryInsurance($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\EligiblePatientView whereState($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\EligiblePatientView whereTertiaryInsurance($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\EligiblePatientView whereZip($value)
 */
class EligiblePatientView extends SqlViewModel
{
    public $phi = [
        'first_name',
        'last_name',
        'mrn',
        'dob',
    ];

    protected $table = 'eligible_patients';
}
