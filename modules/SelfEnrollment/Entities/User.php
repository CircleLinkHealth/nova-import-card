<?php

namespace CircleLinkHealth\SelfEnrollment\Entities;

use CircleLinkHealth\SelfEnrollment\Traits\SelfEnrollableTrait;

/**
 * CircleLinkHealth\SelfEnrollment\Entities\User
 *
 * @property int $id
 * @property int|null $saas_account_id
 * @property int $skip_browser_checks Skip compatible browser checks when the user logs in
 * @property int $count_ccm_time
 * @property string|null $username
 * @property int|null $program_id
 * @property string|null $scope
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
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $last_login
 * @property int $is_online
 * @property string|null $last_session_id
 * @property-read \CircleLinkHealth\SharedModels\Entities\CareplanAssessment|null $carePlanAssessment
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\SharedModels\Entities\Allergy[] $ccdAllergies
 * @property-read int|null $ccd_allergies_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\SharedModels\Entities\CcdInsurancePolicy[] $ccdInsurancePolicies
 * @property-read int|null $ccd_insurance_policies_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\SharedModels\Entities\Medication[] $ccdMedications
 * @property-read int|null $ccd_medications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\SharedModels\Entities\Problem[] $ccdProblems
 * @property-read int|null $ccd_problems_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Passport\Client[] $clients
 * @property-read int|null $clients_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\SharedModels\Entities\CpmBiometric[] $cpmBiometrics
 * @property-read int|null $cpm_biometrics_count
 * @property-read \CircleLinkHealth\SharedModels\Entities\CpmBloodPressure|null $cpmBloodPressure
 * @property-read \CircleLinkHealth\SharedModels\Entities\CpmBloodSugar|null $cpmBloodSugar
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
 * @property-read \CircleLinkHealth\SharedModels\Entities\CpmSmoking|null $cpmSmoking
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\SharedModels\Entities\CpmSymptom[] $cpmSymptoms
 * @property-read int|null $cpm_symptoms_count
 * @property-read \CircleLinkHealth\SharedModels\Entities\CpmWeight|null $cpmWeight
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\SharedModels\Entities\Dispute[] $disputes
 * @property-read int|null $disputes_count
 * @property-read \CircleLinkHealth\SharedModels\Entities\TargetPatient|null $ehrInfo
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Customer\Entities\EmrDirectAddress[] $emrDirect
 * @property-read int|null $emr_direct_count
 * @property-read \CircleLinkHealth\SelfEnrollment\EnrollableRequestInfo\EnrollableRequestInfo|null $enrollableInfoRequest
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\SelfEnrollment\EnrollableInvitationLink\EnrollableInvitationLink[] $enrollmentInvitationLinks
 * @property-read int|null $enrollment_invitation_links_count
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
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\SharedModels\Entities\Call[] $inboundCalls
 * @property-read int|null $inbound_calls_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Customer\Entities\Media[] $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\CircleLinkHealth\Core\Entities\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\SharedModels\Entities\NurseInvoiceExtra[] $nurseBonuses
 * @property-read int|null $nurse_bonuses_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\SharedModels\Entities\Call[] $outboundCalls
 * @property-read int|null $outbound_calls_count
 * @property-read \CircleLinkHealth\Customer\Entities\UserPasswordsHistory|null $passwordsHistory
 * @property-read \CircleLinkHealth\Customer\Entities\Patient|null $patientInfo
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Customer\Entities\Permission[] $perms
 * @property-read int|null $perms_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Revisionable\Entities\Revision[] $revisionHistory
 * @property-read int|null $revision_history_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Customer\Entities\Role[] $roles
 * @property-read int|null $roles_count
 * @property-read \CircleLinkHealth\Customer\Entities\SaasAccount|null $saasAccount
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Passport\Token[] $tokens
 * @property-read int|null $tokens_count
 * @method static \Illuminate\Database\Eloquent\Builder|User activeNurses()
 * @method static \Illuminate\Database\Eloquent\Builder|User careCoaches()
 * @method static \Illuminate\Database\Eloquent\Builder|User exceptType($type)
 * @method static \Illuminate\Database\Eloquent\Builder|User filter(\CircleLinkHealth\Core\Filters\QueryFilters $filters)
 * @method static \Illuminate\Database\Eloquent\Builder|User hasBhiConsent()
 * @method static \Illuminate\Database\Eloquent\Builder|User hasBillingProvider($billing_provider_id)
 * @method static \Illuminate\Database\Eloquent\Builder|User hasSelfEnrollmentInvite(?\Carbon\Carbon $date = null, $has = true)
 * @method static \Illuminate\Database\Eloquent\Builder|User hasSelfEnrollmentInviteReminder(?\Carbon\Carbon $date = null, $has = true)
 * @method static \Illuminate\Database\Eloquent\Builder|User haveEnrollableInvitationDontHaveReminder(?\Carbon\Carbon $dateInviteSent = null)
 * @method static \Illuminate\Database\Eloquent\Builder|User intersectLocationsWith($user)
 * @method static \Illuminate\Database\Eloquent\Builder|User intersectPracticesWith(\CircleLinkHealth\Customer\Entities\User $user, bool $withDemo = true)
 * @method static \Illuminate\Database\Eloquent\Builder|User isBhiChargeable()
 * @method static \Illuminate\Database\Eloquent\Builder|User isBhiEligible()
 * @method static \Illuminate\Database\Eloquent\Builder|User isNotDemo()
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User notOfPracticeRequiringSpecialBhiConsent()
 * @method static \Illuminate\Database\Eloquent\Builder|User ofActiveBillablePractice(bool $includeDemo = true)
 * @method static \Illuminate\Database\Eloquent\Builder|User ofPractice($practiceId)
 * @method static \Illuminate\Database\Eloquent\Builder|User ofPracticeRequiringSpecialBhiConsent()
 * @method static \Illuminate\Database\Eloquent\Builder|User ofType($type, $excludeAwv = true)
 * @method static \Illuminate\Database\Eloquent\Builder|User ofTypePatients()
 * @method static \Illuminate\Database\Eloquent\Builder|User patientsPendingCLHApproval(\CircleLinkHealth\Customer\Entities\User $approver)
 * @method static \Illuminate\Database\Eloquent\Builder|User patientsPendingProviderApproval(\CircleLinkHealth\Customer\Entities\User $approver)
 * @method static \Illuminate\Database\Eloquent\Builder|User practiceStaff()
 * @method static \Illuminate\Database\Eloquent\Builder|User practicesWhereHasRoles(array $roleIds, bool $onlyActive = false)
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User searchPhoneNumber(array $phones)
 * @method static \Illuminate\Database\Eloquent\Builder|User uniquePatients()
 * @method static \Illuminate\Database\Eloquent\Builder|User withCareTeamOfType($type)
 * @method static \Illuminate\Database\Eloquent\Builder|User withDownloadableInvoices(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\SharedModels\Entities\Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\SharedModels\Entities\Activity[] $activitiesAsProvider
 * @property-read int|null $activities_as_provider_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Customer\Entities\Appointment[] $appointments
 * @property-read int|null $appointments_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\SharedModels\Entities\Enrollee[] $assignedEnrollees
 * @property-read int|null $assigned_enrollees_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\CcmBilling\Entities\AttestedProblem[] $attestedProblems
 * @property-read int|null $attested_problems_count
 * @property-read \CircleLinkHealth\TwoFA\Entities\AuthyUser|null $authyUser
 * @property-read \CircleLinkHealth\Customer\Entities\CareAmbassador|null $careAmbassador
 * @property-read \CircleLinkHealth\SharedModels\Entities\CarePlan|null $carePlan
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Customer\Entities\CarePerson[] $careTeamMembers
 * @property-read int|null $care_team_members_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\SharedModels\Entities\Ccda[] $ccdas
 * @property-read int|null $ccdas_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary[] $chargeableMonthlySummaries
 * @property-read int|null $chargeable_monthly_summaries_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummaryView[] $chargeableMonthlySummariesView
 * @property-read int|null $chargeable_monthly_summaries_view_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Customer\Entities\ChargeableService[] $chargeableServices
 * @property-read int|null $chargeable_services_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Customer\Entities\Location[] $clinicalEmergencyContactLocations
 * @property-read int|null $clinical_emergency_contact_locations_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\SharedModels\Entities\Comment[] $comment
 * @property-read int|null $comment_count
 * @property-read \CircleLinkHealth\Customer\Entities\EhrReportWriterInfo|null $ehrReportWriterInfo
 * @property-read \CircleLinkHealth\SharedModels\Entities\EmailSettings|null $emailSettings
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\CcmBilling\Entities\EndOfMonthCcmStatusLog[] $endOfMonthCcmStatusLogs
 * @property-read int|null $end_of_month_ccm_status_logs_count
 * @property-read \CircleLinkHealth\SharedModels\Entities\Enrollee|null $enrollee
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\SharedModels\Entities\ForeignId[] $foreignId
 * @property-read int|null $foreign_id_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\SharedModels\Entities\Call[] $inboundActivities
 * @property-read int|null $inbound_activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\SharedModels\Entities\Message[] $inboundMessages
 * @property-read int|null $inbound_messages_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Customer\Entities\Location[] $locations
 * @property-read int|null $locations_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\SharedModels\Entities\LoginLogout[] $loginEvents
 * @property-read int|null $login_events_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\SharedModels\Entities\Note[] $notes
 * @property-read int|null $notes_count
 * @property-read \CircleLinkHealth\Customer\Entities\Nurse|null $nurseInfo
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\SharedModels\Entities\Observation[] $observations
 * @property-read int|null $observations_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\SharedModels\Entities\Message[] $outboundMessages
 * @property-read int|null $outbound_messages_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\SharedModels\Entities\PageTimer[] $pageTimersAsProvider
 * @property-read int|null $page_timers_as_provider_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Customer\Entities\PatientAWVSummary[] $patientAWVSummaries
 * @property-read int|null $patient_a_w_v_summaries_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\SharedModels\Entities\Activity[] $patientActivities
 * @property-read int|null $patient_activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Customer\Entities\PatientCcmStatusRevision[] $patientCcmStatusRevisions
 * @property-read int|null $patient_ccm_status_revisions_count
 * @property-read \CircleLinkHealth\Customer\Entities\PatientNurse|null $patientNurseAsPatient
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary[] $patientSummaries
 * @property-read int|null $patient_summaries_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Customer\Entities\PhoneNumber[] $phoneNumbers
 * @property-read int|null $phone_numbers_count
 * @property-read \CircleLinkHealth\Customer\Entities\Practice|null $primaryPractice
 * @property-read \CircleLinkHealth\Customer\Entities\ProviderInfo|null $providerInfo
 * @property-read \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\SamlSp\Entities\SamlUser[] $samlUsers
 * @property-read int|null $saml_users_count
 */
class User extends \CircleLinkHealth\Customer\Entities\User
{
    protected $fillable = [
    ];

    protected $guarded = [

    ];

    use SelfEnrollableTrait;
}
