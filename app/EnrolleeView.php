<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Core\Entities\SqlViewModel;
use CircleLinkHealth\Core\Filters\Filterable;

/**
 * App\EnrolleeView.
 *
 * @property int                             $id
 * @property int|null                        $batch_id
 * @property int|null                        $eligibility_job_id
 * @property string|null                     $medical_record_type
 * @property int|null                        $medical_record_id
 * @property int|null                        $user_id
 * @property int|null                        $provider_id
 * @property int|null                        $practice_id
 * @property int|null                        $care_ambassador_user_id
 * @property int                             $total_time_spent
 * @property string|null                     $last_call_outcome
 * @property string|null                     $last_call_outcome_reason
 * @property string|null                     $mrn
 * @property string                          $first_name
 * @property string                          $last_name
 * @property string|null                     $address
 * @property string|null                     $address_2
 * @property string|null                     $city
 * @property string|null                     $state
 * @property string|null                     $zip
 * @property string|null                     $primary_phone
 * @property string|null                     $other_phone
 * @property string|null                     $home_phone
 * @property string|null                     $cell_phone
 * @property string|null                     $dob
 * @property string|null                     $lang
 * @property string|null                     $invite_code
 * @property string|null                     $status
 * @property int|null                        $attempt_count
 * @property string|null                     $preferred_days
 * @property string|null                     $preferred_window
 * @property string|null                     $invite_sent_at
 * @property string|null                     $consented_at
 * @property string|null                     $last_attempt_at
 * @property string|null                     $invite_opened_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null                     $primary_insurance
 * @property string|null                     $secondary_insurance
 * @property string|null                     $tertiary_insurance
 * @property int|null                        $has_copay
 * @property string|null                     $email
 * @property string|null                     $last_encounter
 * @property string                          $referring_provider_name
 * @property int|null                        $confident_provider_guess
 * @property string                          $problems
 * @property int                             $cpm_problem_1
 * @property int|null                        $cpm_problem_2
 * @property string|null                     $color
 * @property string|null                     $soft_rejected_callback
 * @property string|null                     $provider_name
 * @property string|null                     $care_ambassador_name
 * @property string|null                     $practice_name
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView filter(\App\Filters\QueryFilters $filters)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView newModelQuery()
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView newQuery()
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView query()
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereAddress($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereAddress2($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereAttemptCount($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereBatchId($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereCareAmbassadorName($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereCareAmbassadorUserId($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereCellPhone($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereCity($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereColor($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereConfidentProviderGuess($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereConsentedAt($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereCpmProblem1($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereCpmProblem2($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereCreatedAt($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereDob($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereEligibilityJobId($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereEmail($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereFirstName($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereHasCopay($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereHomePhone($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereId($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereInviteCode($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereInviteOpenedAt($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereInviteSentAt($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereLang($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereLastAttemptAt($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereLastCallOutcome($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereLastCallOutcomeReason($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereLastEncounter($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereLastName($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereMedicalRecordId($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereMedicalRecordType($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereMrn($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereOtherPhone($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView wherePracticeId($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView wherePracticeName($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView wherePreferredDays($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView wherePreferredWindow($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView wherePrimaryInsurance($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView wherePrimaryPhone($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereProblems($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereProviderId($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereProviderName($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereReferringProviderName($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereSecondaryInsurance($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereSoftRejectedCallback($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereState($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereStatus($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereTertiaryInsurance($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereTotalTimeSpent($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereUpdatedAt($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereUserId($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereZip($value)
 * @mixin \Eloquent
 * @property string|null                                                                                 $requested_callback
 * @property string|null                                                                                 $provider_sex
 * @property string|null                                                                                 $provider_pronunciation
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereProviderPronunciation($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereProviderSex($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereRequestedCallback($value)
 * @property mixed|null                                                                                  $agent_details
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereAgentDetails($value)
 * @property int|null                                                                                    $family_enrollee_id
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereFamilyEnrolleeId($value)
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @property int|null                                                                                    $revision_history_count
 * @property int|null                                                                                    $enrollment_non_responsive
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereEnrollmentNonResponsive($value)
 * @property int|null                                                                                    $location_id
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereLocationId($value)
 * @property string|null                                                                                 $other_note
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\EnrolleeView whereOtherNote($value)
 * @property int                                                                                         $auto_enrollment_triggered
 * @property string|null                                                                                 $source
 * @property string|null                                                                                 $callback_note
 * @property int                                                                                         $invited
 */
class EnrolleeView extends SqlViewModel
{
    use Filterable;

    public $phi = [
        'first_name',
        'last_name',
        'address',
        'address_2',
        'city',
        'state',
        'zip',
        'primary_phone',
        'cell_phone',
        'home_phone',
        'other_phone',
        'primary_insurance',
        'secondary_insurance',
        'tertiary_insurance',
        'has_copay',
        'email',
        'agent_details',
    ];

    protected $table = 'enrollees_view';
}
