<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

/**
 * App\User
 *
 * @property int $id
 * @property int|null $saas_account_id
 * @property int $skip_browser_checks Skip compatible browser checks when the user logs in
 * @property int $count_ccm_time
 * @property string|null $username
 * @property int|null $program_id
 * @property string|null $password
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $user_registered
 * @property int|null $user_status
 * @property int|null $auto_attach_programs
 * @property string|null $display_name
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $suffix
 * @property string|null $address
 * @property string|null $address2
 * @property string|null $city
 * @property string|null $state
 * @property string|null $zip
 * @property string|null $timezone
 * @property string|null $status
 * @property int $access_disabled
 * @property int $is_auto_generated
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property string|null $last_login
 * @property int $is_online
 * @property string|null $last_session_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Nova\Actions\ActionEvent[] $actions
 * @property-read int|null $actions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\TimeTracking\Entities\Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\TimeTracking\Entities\Activity[] $activitiesAsProvider
 * @property-read int|null $activities_as_provider_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Customer\Entities\Appointment[] $appointments
 * @property-read int|null $appointments_count
 * @property-read \CircleLinkHealth\TwoFA\Entities\AuthyUser $authyUser
 * @property-read \App\CareAmbassador $careAmbassador
 * @property-read \CircleLinkHealth\SharedModels\Entities\CarePlan $carePlan
 * @property-read \App\CareplanAssessment $carePlanAssessment
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Customer\Entities\CarePerson[] $careTeamMembers
 * @property-read int|null $care_team_members_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\SharedModels\Entities\Allergy[] $ccdAllergies
 * @property-read int|null $ccd_allergies_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\SharedModels\Entities\CcdInsurancePolicy[] $ccdInsurancePolicies
 * @property-read int|null $ccd_insurance_policies_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\SharedModels\Entities\Medication[] $ccdMedications
 * @property-read int|null $ccd_medications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\SharedModels\Entities\Problem[] $ccdProblems
 * @property-read int|null $ccd_problems_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\SharedModels\Entities\Ccda[] $ccdas
 * @property-read int|null $ccdas_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Customer\Entities\ChargeableService[] $chargeableServices
 * @property-read int|null $chargeable_services_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Passport\Client[] $clients
 * @property-read int|null $clients_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Customer\Entities\Location[] $clinicalEmergencyContactLocations
 * @property-read int|null $clinical_emergency_contact_locations_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Comment[] $comment
 * @property-read int|null $comment_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\SharedModels\Entities\CpmBiometric[] $cpmBiometrics
 * @property-read int|null $cpm_biometrics_count
 * @property-read \CircleLinkHealth\SharedModels\Entities\CpmBloodPressure $cpmBloodPressure
 * @property-read \CircleLinkHealth\SharedModels\Entities\CpmBloodSugar $cpmBloodSugar
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\SharedModels\Entities\CpmLifestyle[] $cpmLifestyles
 * @property-read int|null $cpm_lifestyles_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\SharedModels\Entities\CpmMedicationGroup[] $cpmMedicationGroups
 * @property-read int|null $cpm_medication_groups_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\SharedModels\Entities\CpmMiscUser[] $cpmMiscUserPivot
 * @property-read int|null $cpm_misc_user_pivot_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\SharedModels\Entities\CpmMisc[] $cpmMiscs
 * @property-read int|null $cpm_miscs_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\SharedModels\Entities\CpmProblem[] $cpmProblems
 * @property-read int|null $cpm_problems_count
 * @property-read \CircleLinkHealth\SharedModels\Entities\CpmSmoking $cpmSmoking
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\SharedModels\Entities\CpmSymptom[] $cpmSymptoms
 * @property-read int|null $cpm_symptoms_count
 * @property-read \CircleLinkHealth\SharedModels\Entities\CpmWeight $cpmWeight
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\NurseInvoices\Entities\Dispute[] $disputes
 * @property-read int|null $disputes_count
 * @property-read \CircleLinkHealth\Eligibility\Entities\TargetPatient $ehrInfo
 * @property-read \CircleLinkHealth\Customer\Entities\EhrReportWriterInfo $ehrReportWriterInfo
 * @property-read \App\Models\EmailSettings $emailSettings
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Customer\Entities\EmrDirectAddress[] $emrDirect
 * @property-read int|null $emr_direct_count
 * @property-read \App\EnrollableInvitationLink $enrollmentInvitationLink
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ForeignId[] $foreignId
 * @property-read int|null $foreign_id_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Customer\Entities\User[] $forwardAlertsTo
 * @property-read int|null $forward_alerts_to_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Customer\Entities\User[] $forwardedAlertsBy
 * @property-read int|null $forwarded_alerts_by_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Customer\Entities\User[] $forwardedCarePlanApprovalEmailsBy
 * @property-read int|null $forwarded_care_plan_approval_emails_by_count
 * @property mixed $emr_direct_address
 * @property-read mixed $full_name_with_id
 * @property-read string $name
 * @property-read mixed $timezone_abbr
 * @property-read mixed $timezone_offset
 * @property-read mixed $timezone_offset_hours
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Call[] $inboundActivities
 * @property-read int|null $inbound_activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Call[] $inboundCalls
 * @property-read int|null $inbound_calls_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Message[] $inboundMessages
 * @property-read int|null $inbound_messages_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Customer\Entities\Location[] $locations
 * @property-read int|null $locations_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Customer\Entities\Media[] $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Note[] $notes
 * @property-read int|null $notes_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\CircleLinkHealth\Core\Entities\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoiceExtra[] $nurseBonuses
 * @property-read int|null $nurse_bonuses_count
 * @property-read \CircleLinkHealth\Customer\Entities\Nurse $nurseInfo
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Observation[] $observations
 * @property-read int|null $observations_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Call[] $outboundCalls
 * @property-read int|null $outbound_calls_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Message[] $outboundMessages
 * @property-read int|null $outbound_messages_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\TimeTracking\Entities\PageTimer[] $pageTimersAsProvider
 * @property-read int|null $page_timers_as_provider_count
 * @property-read \CircleLinkHealth\Customer\Entities\UserPasswordsHistory $passwordsHistory
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Customer\Entities\PatientAWVSummary[] $patientAWVSummaries
 * @property-read int|null $patient_a_w_v_summaries_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\TimeTracking\Entities\Activity[] $patientActivities
 * @property-read int|null $patient_activities_count
 * @property-read \CircleLinkHealth\Customer\Entities\Patient $patientInfo
 * @property-read \CircleLinkHealth\Customer\Entities\PatientNurse $patientNurseAsPatient
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary[] $patientSummaries
 * @property-read int|null $patient_summaries_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Customer\Entities\Permission[] $perms
 * @property-read int|null $perms_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Customer\Entities\PhoneNumber[] $phoneNumbers
 * @property-read int|null $phone_numbers_count
 * @property-read \CircleLinkHealth\Customer\Entities\Practice|null $primaryPractice
 * @property-read \CircleLinkHealth\Customer\Entities\ProviderInfo $providerInfo
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Revisionable\Entities\Revision[] $revisionHistory
 * @property-read int|null $revision_history_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Customer\Entities\Role[] $roles
 * @property-read int|null $roles_count
 * @property-read \CircleLinkHealth\Customer\Entities\SaasAccount|null $saasAccount
 * @property-read \App\EnrollableRequestInfo $statusRequestsInfo
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Passport\Token[] $tokens
 * @property-read int|null $tokens_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CPRulesUCP[] $ucp
 * @property-read int|null $ucp_count
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User careCoaches()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User exceptType($type)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User filter(\App\Filters\QueryFilters $filters)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User hasBillingProvider($billing_provider_id)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User intersectLocationsWith($user)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User intersectPracticesWith(\CircleLinkHealth\Customer\Entities\User $user, $withDemo = true)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User isBhiChargeable()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User isBhiEligible()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User isNotDemo()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User notOfPracticeRequiringSpecialBhiConsent()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User ofActiveBillablePractice()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User ofPractice($practiceId)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User ofPracticeRequiringSpecialBhiConsent()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User ofType($type, $excludeAwv = true)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User practiceStaff()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User practicesWhereHasRoles($roleIds, $onlyActive = false)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereAccessDisabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereAutoAttachPrograms($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCountCcmTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereIsAutoGenerated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereIsOnline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereLastLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereLastSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereProgramId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereSaasAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereSkipBrowserChecks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereSuffix($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUserRegistered($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUserStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereZip($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User withCareTeamOfType($type)
 * @mixin \Eloquent
 */
class User extends \CircleLinkHealth\Customer\Entities\User
{
}
