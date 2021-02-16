<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use Laravel\Nova\Actions\Actionable;

/**
 * App\User.
 *
 * @property int                                                                                                                     $id
 * @property int|null                                                                                                                $saas_account_id
 * @property int                                                                                                                     $skip_browser_checks                                                                                                                     Skip compatible browser checks when the user logs in
 * @property int                                                                                                                     $count_ccm_time
 * @property string|null                                                                                                             $username
 * @property int|null                                                                                                                $program_id
 * @property string|null                                                                                                             $scope
 * @property string|null                                                                                                             $password
 * @property string                                                                                                                  $email
 * @property \Illuminate\Support\Carbon|null                                                                                         $user_registered
 * @property int|null                                                                                                                $user_status
 * @property int|null                                                                                                                $auto_attach_programs
 * @property string|null                                                                                                             $display_name
 * @property string|null                                                                                                             $first_name
 * @property string|null                                                                                                             $last_name
 * @property string|null                                                                                                             $suffix
 * @property string|null                                                                                                             $address
 * @property string|null                                                                                                             $address2
 * @property string|null                                                                                                             $city
 * @property string|null                                                                                                             $state
 * @property string|null                                                                                                             $zip
 * @property string|null                                                                                                             $timezone
 * @property string|null                                                                                                             $status
 * @property int                                                                                                                     $access_disabled
 * @property int                                                                                                                     $is_auto_generated
 * @property string|null                                                                                                             $remember_token
 * @property \Illuminate\Support\Carbon|null                                                                                         $created_at
 * @property \Illuminate\Support\Carbon|null                                                                                         $updated_at
 * @property \Illuminate\Support\Carbon|null                                                                                         $deleted_at
 * @property string|null                                                                                                             $last_login
 * @property int                                                                                                                     $is_online
 * @property string|null                                                                                                             $last_session_id
 * @property \Illuminate\Database\Eloquent\Collection|\Laravel\Nova\Actions\ActionEvent[]                                            $actions
 * @property int|null                                                                                                                $actions_count
 * @property \CircleLinkHealth\SharedModels\Entities\Activity[]|\Illuminate\Database\Eloquent\Collection                             $activities
 * @property int|null                                                                                                                $activities_count
 * @property \CircleLinkHealth\SharedModels\Entities\Activity[]|\Illuminate\Database\Eloquent\Collection                             $activitiesAsProvider
 * @property int|null                                                                                                                $activities_as_provider_count
 * @property \CircleLinkHealth\Customer\Entities\Appointment[]|\Illuminate\Database\Eloquent\Collection                              $appointments
 * @property int|null                                                                                                                $appointments_count
 * @property \CircleLinkHealth\SharedModels\Entities\Enrollee[]|\Illuminate\Database\Eloquent\Collection                             $assignedEnrollees
 * @property int|null                                                                                                                $assigned_enrollees_count
 * @property \CircleLinkHealth\CcmBilling\Entities\AttestedProblem[]|\Illuminate\Database\Eloquent\Collection                        $attestedProblems
 * @property int|null                                                                                                                $attested_problems_count
 * @property \CircleLinkHealth\TwoFA\Entities\AuthyUser|null                                                                         $authyUser
 * @property \CircleLinkHealth\Customer\Entities\CareAmbassador|null                                                                 $careAmbassador
 * @property \CircleLinkHealth\SharedModels\Entities\CarePlan|null                                                                   $carePlan
 * @property \CircleLinkHealth\SharedModels\Entities\CareplanAssessment|null                                                         $carePlanAssessment
 * @property \CircleLinkHealth\Customer\Entities\CarePerson[]|\Illuminate\Database\Eloquent\Collection                               $careTeamMembers
 * @property int|null                                                                                                                $care_team_members_count
 * @property \CircleLinkHealth\SharedModels\Entities\Allergy[]|\Illuminate\Database\Eloquent\Collection                              $ccdAllergies
 * @property int|null                                                                                                                $ccd_allergies_count
 * @property \CircleLinkHealth\SharedModels\Entities\CcdInsurancePolicy[]|\Illuminate\Database\Eloquent\Collection                   $ccdInsurancePolicies
 * @property int|null                                                                                                                $ccd_insurance_policies_count
 * @property \CircleLinkHealth\SharedModels\Entities\Medication[]|\Illuminate\Database\Eloquent\Collection                           $ccdMedications
 * @property int|null                                                                                                                $ccd_medications_count
 * @property \CircleLinkHealth\SharedModels\Entities\Problem[]|\Illuminate\Database\Eloquent\Collection                              $ccdProblems
 * @property int|null                                                                                                                $ccd_problems_count
 * @property \CircleLinkHealth\SharedModels\Entities\Ccda[]|\Illuminate\Database\Eloquent\Collection                                 $ccdas
 * @property int|null                                                                                                                $ccdas_count
 * @property \CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary[]|\Illuminate\Database\Eloquent\Collection        $chargeableMonthlySummaries
 * @property int|null                                                                                                                $chargeable_monthly_summaries_count
 * @property \CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummaryView[]|\Illuminate\Database\Eloquent\Collection    $chargeableMonthlySummariesView
 * @property int|null                                                                                                                $chargeable_monthly_summaries_view_count
 * @property \CircleLinkHealth\Customer\Entities\ChargeableService[]|\Illuminate\Database\Eloquent\Collection                        $chargeableServices
 * @property int|null                                                                                                                $chargeable_services_count
 * @property \Illuminate\Database\Eloquent\Collection|\Laravel\Passport\Client[]                                                     $clients
 * @property int|null                                                                                                                $clients_count
 * @property \CircleLinkHealth\Customer\Entities\Location[]|\Illuminate\Database\Eloquent\Collection                                 $clinicalEmergencyContactLocations
 * @property int|null                                                                                                                $clinical_emergency_contact_locations_count
 * @property \CircleLinkHealth\SharedModels\Entities\CpmBiometric[]|\Illuminate\Database\Eloquent\Collection                         $cpmBiometrics
 * @property int|null                                                                                                                $cpm_biometrics_count
 * @property \CircleLinkHealth\SharedModels\Entities\CpmLifestyle[]|\Illuminate\Database\Eloquent\Collection                         $cpmLifestyles
 * @property int|null                                                                                                                $cpm_lifestyles_count
 * @property \CircleLinkHealth\SharedModels\Entities\CpmMedicationGroup[]|\Illuminate\Database\Eloquent\Collection                   $cpmMedicationGroups
 * @property int|null                                                                                                                $cpm_medication_groups_count
 * @property \CircleLinkHealth\SharedModels\Entities\CpmMiscUser[]|\Illuminate\Database\Eloquent\Collection                          $cpmMiscUserPivot
 * @property int|null                                                                                                                $cpm_misc_user_pivot_count
 * @property \CircleLinkHealth\SharedModels\Entities\CpmMisc[]|\Illuminate\Database\Eloquent\Collection                              $cpmMiscs
 * @property int|null                                                                                                                $cpm_miscs_count
 * @property \CircleLinkHealth\SharedModels\Entities\CpmProblem[]|\Illuminate\Database\Eloquent\Collection                           $cpmProblems
 * @property int|null                                                                                                                $cpm_problems_count
 * @property \CircleLinkHealth\SharedModels\Entities\CpmSymptom[]|\Illuminate\Database\Eloquent\Collection                           $cpmSymptoms
 * @property int|null                                                                                                                $cpm_symptoms_count
 * @property \CircleLinkHealth\SharedModels\Entities\Dispute[]|\Illuminate\Database\Eloquent\Collection                              $disputes
 * @property int|null                                                                                                                $disputes_count
 * @property \CircleLinkHealth\SharedModels\Entities\TargetPatient|null                                                               $ehrInfo
 * @property \CircleLinkHealth\Customer\Entities\EhrReportWriterInfo|null                                                            $ehrReportWriterInfo
 * @property \CircleLinkHealth\Customer\Entities\EmrDirectAddress[]|\Illuminate\Database\Eloquent\Collection                         $emrDirect
 * @property int|null                                                                                                                $emr_direct_count
 * @property \CircleLinkHealth\CcmBilling\Entities\EndOfMonthCcmStatusLog[]|\Illuminate\Database\Eloquent\Collection                 $endOfMonthCcmStatusLogs
 * @property int|null                                                                                                                $end_of_month_ccm_status_logs_count
 * @property \CircleLinkHealth\Customer\EnrollableRequestInfo\EnrollableRequestInfo|null                                             $enrollableInfoRequest
 * @property \CircleLinkHealth\SharedModels\Entities\Enrollee|null                                                                   $enrollee
 * @property \CircleLinkHealth\Customer\EnrollableInvitationLink\EnrollableInvitationLink[]|\Illuminate\Database\Eloquent\Collection $enrollmentInvitationLinks
 * @property int|null                                                                                                                $enrollment_invitation_links_count
 * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection                                     $forwardAlertsTo
 * @property int|null                                                                                                                $forward_alerts_to_count
 * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection                                     $forwardedAlertsBy
 * @property int|null                                                                                                                $forwarded_alerts_by_count
 * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection                                     $forwardedCarePlanApprovalEmailsBy
 * @property int|null                                                                                                                $forwarded_care_plan_approval_emails_by_count
 * @property mixed                                                                                                                   $emr_direct_address
 * @property mixed                                                                                                                   $full_name_with_id
 * @property string                                                                                                                  $name
 * @property mixed                                                                                                                   $timezone_abbr
 * @property mixed                                                                                                                   $timezone_offset
 * @property mixed                                                                                                                   $timezone_offset_hours
 * @property \CircleLinkHealth\SharedModels\Entities\Call[]|\Illuminate\Database\Eloquent\Collection                                 $inboundActivities
 * @property int|null                                                                                                                $inbound_activities_count
 * @property \CircleLinkHealth\SharedModels\Entities\Call[]|\Illuminate\Database\Eloquent\Collection                                 $inboundCalls
 * @property int|null                                                                                                                $inbound_calls_count
 * @property \CircleLinkHealth\Customer\Entities\Location[]|\Illuminate\Database\Eloquent\Collection                                 $locations
 * @property int|null                                                                                                                $locations_count
 * @property \CircleLinkHealth\SharedModels\Entities\LoginLogout[]|\Illuminate\Database\Eloquent\Collection                          $loginEvents
 * @property int|null                                                                                                                $login_events_count
 * @property \CircleLinkHealth\Customer\Entities\Media[]|\Illuminate\Database\Eloquent\Collection                                    $media
 * @property int|null                                                                                                                $media_count
 * @property \CircleLinkHealth\SharedModels\Entities\Note[]|\Illuminate\Database\Eloquent\Collection                                 $notes
 * @property int|null                                                                                                                $notes_count
 * @property \CircleLinkHealth\Core\Entities\DatabaseNotification[]|\Illuminate\Notifications\DatabaseNotificationCollection         $notifications
 * @property int|null                                                                                                                $notifications_count
 * @property \CircleLinkHealth\SharedModels\Entities\NurseInvoiceExtra[]|\Illuminate\Database\Eloquent\Collection                    $nurseBonuses
 * @property int|null                                                                                                                $nurse_bonuses_count
 * @property \CircleLinkHealth\Customer\Entities\Nurse|null                                                                          $nurseInfo
 * @property \CircleLinkHealth\SharedModels\Entities\Observation[]|\Illuminate\Database\Eloquent\Collection                          $observations
 * @property int|null                                                                                                                $observations_count
 * @property \CircleLinkHealth\SharedModels\Entities\Call[]|\Illuminate\Database\Eloquent\Collection                                 $outboundCalls
 * @property int|null                                                                                                                $outbound_calls_count
 * @property \CircleLinkHealth\SharedModels\Entities\PageTimer[]|\Illuminate\Database\Eloquent\Collection                            $pageTimersAsProvider
 * @property int|null                                                                                                                $page_timers_as_provider_count
 * @property \CircleLinkHealth\Customer\Entities\UserPasswordsHistory|null                                                           $passwordsHistory
 * @property \CircleLinkHealth\Customer\Entities\PatientAWVSummary[]|\Illuminate\Database\Eloquent\Collection                        $patientAWVSummaries
 * @property int|null                                                                                                                $patient_a_w_v_summaries_count
 * @property \CircleLinkHealth\SharedModels\Entities\Activity[]|\Illuminate\Database\Eloquent\Collection                             $patientActivities
 * @property int|null                                                                                                                $patient_activities_count
 * @property \CircleLinkHealth\Customer\Entities\PatientCcmStatusRevision[]|\Illuminate\Database\Eloquent\Collection                 $patientCcmStatusRevisions
 * @property int|null                                                                                                                $patient_ccm_status_revisions_count
 * @property \CircleLinkHealth\Customer\Entities\Patient|null                                                                        $patientInfo
 * @property \CircleLinkHealth\Customer\Entities\PatientNurse|null                                                                   $patientNurseAsPatient
 * @property \CircleLinkHealth\Customer\Entities\PatientMonthlySummary[]|\Illuminate\Database\Eloquent\Collection                    $patientSummaries
 * @property int|null                                                                                                                $patient_summaries_count
 * @property \CircleLinkHealth\Customer\Entities\Permission[]|\Illuminate\Database\Eloquent\Collection                               $perms
 * @property int|null                                                                                                                $perms_count
 * @property \CircleLinkHealth\Customer\Entities\PhoneNumber[]|\Illuminate\Database\Eloquent\Collection                              $phoneNumbers
 * @property int|null                                                                                                                $phone_numbers_count
 * @property \CircleLinkHealth\Customer\Entities\Practice|null                                                                       $primaryPractice
 * @property \CircleLinkHealth\Customer\Entities\ProviderInfo|null                                                                   $providerInfo
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection                             $revisionHistory
 * @property int|null                                                                                                                $revision_history_count
 * @property \CircleLinkHealth\Customer\Entities\Role[]|\Illuminate\Database\Eloquent\Collection                                     $roles
 * @property int|null                                                                                                                $roles_count
 * @property \CircleLinkHealth\Customer\Entities\SaasAccount|null                                                                    $saasAccount
 * @property \CircleLinkHealth\SamlSp\Entities\SamlUser[]|\Illuminate\Database\Eloquent\Collection                                   $samlUsers
 * @property int|null                                                                                                                $saml_users_count
 * @property \Illuminate\Database\Eloquent\Collection|\Laravel\Passport\Token[]                                                      $tokens
 * @property int|null                                                                                                                $tokens_count
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User careCoaches()
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User exceptType($type)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User filter(\CircleLinkHealth\Core\Filters\QueryFilters $filters)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User hasBhiConsent()
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User hasBillingProvider($billing_provider_id)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User hasSelfEnrollmentInvite(?\Carbon\Carbon $date = null, $has = true)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User hasSelfEnrollmentInviteReminder(?\Carbon\Carbon $date = null, $has = true)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User haveEnrollableInvitationDontHaveReminder(?\Carbon\Carbon $dateInviteSent = null)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User intersectLocationsWith($user)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User intersectPracticesWith(\CircleLinkHealth\Customer\Entities\User $user, bool $withDemo = true)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User isBhiChargeable()
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User isBhiEligible()
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User isNotDemo()
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User notOfPracticeRequiringSpecialBhiConsent()
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User ofActiveBillablePractice(bool $includeDemo = true)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User ofPractice($practiceId)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User ofPracticeRequiringSpecialBhiConsent()
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User ofType($type, $excludeAwv = true)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User patientsPendingCLHApproval(\CircleLinkHealth\Customer\Entities\User $approver)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User patientsPendingProviderApproval(\CircleLinkHealth\Customer\Entities\User $approver)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User practiceStaff()
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User practicesWhereHasRoles(array $roleIds, bool $onlyActive = false)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User query()
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User searchPhoneNumber(array $phones)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User whereAccessDisabled($value)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User whereAddress($value)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User whereAddress2($value)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User whereAutoAttachPrograms($value)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User whereCity($value)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User whereCountCcmTime($value)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User whereDeletedAt($value)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User whereDisplayName($value)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User whereFirstName($value)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User whereIsAutoGenerated($value)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User whereIsOnline($value)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User whereLastLogin($value)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User whereLastName($value)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User whereLastSessionId($value)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User whereProgramId($value)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User whereSaasAccountId($value)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User whereScope($value)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User whereSkipBrowserChecks($value)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User whereState($value)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User whereStatus($value)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User whereSuffix($value)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User whereTimezone($value)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User whereUserRegistered($value)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User whereUserStatus($value)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User whereUsername($value)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User whereZip($value)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User withCareTeamOfType($type)
 * @method   static                                                                                                                  \Illuminate\Database\Eloquent\Builder|User withDownloadableInvoices(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate)
 * @mixin \Eloquent
 * @property \CircleLinkHealth\SharedModels\Entities\EmailSettings|null                                                   $emailSettings
 * @property \CircleLinkHealth\SharedModels\Entities\ForeignId[]|\Illuminate\Database\Eloquent\Collection                 $foreignId
 * @property int|null                                                                                                     $foreign_id_count
 * @property \CircleLinkHealth\SharedModels\Entities\Message[]|\Illuminate\Database\Eloquent\Collection                   $inboundMessages
 * @property int|null                                                                                                     $inbound_messages_count
 * @property \CircleLinkHealth\SharedModels\Entities\Message[]|\Illuminate\Database\Eloquent\Collection                   $outboundMessages
 * @property int|null                                                                                                     $outbound_messages_count
 * @method   static                                                                                                       \Illuminate\Database\Eloquent\Builder|User ofTypePatients()
 * @property \CircleLinkHealth\SharedModels\Entities\Comment[]|\Illuminate\Database\Eloquent\Collection                   $comment
 * @property int|null                                                                                                     $comment_count
 * @property \CircleLinkHealth\SharedModels\Entities\CpmBloodPressure|null                                                $cpmBloodPressure
 * @property \CircleLinkHealth\SharedModels\Entities\CpmBloodSugar|null                                                   $cpmBloodSugar
 * @property \CircleLinkHealth\SharedModels\Entities\CpmSmoking|null                                                      $cpmSmoking
 * @property \CircleLinkHealth\SharedModels\Entities\CpmWeight|null                                                       $cpmWeight
 * @method   static                                                                                                       \Illuminate\Database\Eloquent\Builder|User activeNurses()
 * @property \CircleLinkHealth\Customer\Entities\ChargeableService[]|\Illuminate\Database\Eloquent\Collection             $forcedChargeableServices
 * @property int|null                                                                                                     $forced_chargeable_services_count
 * @property \CircleLinkHealth\CcmBilling\Entities\PatientMonthlyBillingStatus[]|\Illuminate\Database\Eloquent\Collection $monthlyBillingStatus
 * @property int|null                                                                                                     $monthly_billing_status_count
 * @method   static                                                                                                       \Illuminate\Database\Eloquent\Builder|User patientOfLocation(int $locationId, ?string $ccmStatus = null)
 */
class User extends \CircleLinkHealth\Customer\Entities\User
{
    use Actionable;
}
