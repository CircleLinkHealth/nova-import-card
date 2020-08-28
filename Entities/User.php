<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use App\Call;
use App\CareplanAssessment;
use App\Constants;
use App\ForeignId;
use App\LoginLogout;
use App\Message;
use App\Models\EmailSettings;
use App\Notifications\CarePlanApprovalReminder;
use App\Notifications\ResetPassword;
use App\Repositories\Cache\EmptyUserNotificationList;
use App\Repositories\Cache\UserNotificationList;
use App\Services\UserService;
use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\AttestedProblem;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\CcmBilling\Entities\EndOfMonthCcmStatusLog;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientProblemForProcessing;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Core\Exceptions\InvalidArgumentException;
use CircleLinkHealth\Core\Filters\Filterable;
use CircleLinkHealth\Core\Traits\Notifiable;
use CircleLinkHealth\Customer\AppConfig\PracticesRequiringSpecialBhiConsent;
use CircleLinkHealth\Customer\Rules\PasswordCharacters;
use CircleLinkHealth\Customer\Traits\HasEmrDirectAddress;
use CircleLinkHealth\Customer\Traits\MakesOrReceivesCalls;
use CircleLinkHealth\Customer\Traits\SaasAccountable;
use CircleLinkHealth\Customer\Traits\SelfEnrollableTrait;
use CircleLinkHealth\Customer\Traits\TimezoneTrait;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\Entities\TargetPatient;
use CircleLinkHealth\NurseInvoices\Entities\Dispute;
use CircleLinkHealth\NurseInvoices\Entities\NurseInvoice;
use CircleLinkHealth\NurseInvoices\Entities\NurseInvoiceExtra;
use CircleLinkHealth\NurseInvoices\Helpers\NurseInvoiceDisputeDeadline;
use CircleLinkHealth\SharedModels\Entities\Allergy;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use CircleLinkHealth\SharedModels\Entities\CcdInsurancePolicy;
use CircleLinkHealth\SharedModels\Entities\CpmBiometric;
use CircleLinkHealth\SharedModels\Entities\CpmBloodPressure;
use CircleLinkHealth\SharedModels\Entities\CpmBloodSugar;
use CircleLinkHealth\SharedModels\Entities\CpmLifestyle;
use CircleLinkHealth\SharedModels\Entities\CpmMedicationGroup;
use CircleLinkHealth\SharedModels\Entities\CpmMisc;
use CircleLinkHealth\SharedModels\Entities\CpmMiscUser;
use CircleLinkHealth\SharedModels\Entities\CpmProblem;
use CircleLinkHealth\SharedModels\Entities\CpmSmoking;
use CircleLinkHealth\SharedModels\Entities\CpmSymptom;
use CircleLinkHealth\SharedModels\Entities\CpmWeight;
use CircleLinkHealth\SharedModels\Entities\Medication;
use CircleLinkHealth\SharedModels\Entities\Problem;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;
use CircleLinkHealth\TwoFA\Entities\AuthyUser;
use DateTime;
use Fico7489\Laravel\Pivot\Traits\PivotEventTrait;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Lab404\Impersonate\Models\Impersonate;
use Laravel\Passport\HasApiTokens;
use Laravel\Scout\Searchable;
use Michalisantoniou6\Cerberus\Traits\CerberusSiteUserTrait;
use Propaganistas\LaravelPhone\Exceptions\NumberParseException;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

/**
 * CircleLinkHealth\Customer\Entities\User.
 *
 * @property int                                                                                                             $id
 * @property int|null                                                                                                        $saas_account_id
 * @property int                                                                                                             $skip_browser_checks                                                                                                                                                    Skip compatible browser checks when the user logs in
 * @property int                                                                                                             $count_ccm_time
 * @property string                                                                                                          $username
 * @property string                                                                                                          $program_id
 * @property string                                                                                                          $password
 * @property string                                                                                                          $email
 * @property \Illuminate\Support\Carbon|null                                                                                 $user_registered
 * @property int|null                                                                                                        $user_status
 * @property int                                                                                                             $auto_attach_programs
 * @property string                                                                                                          $display_name
 * @property string                                                                                                          $first_name
 * @property string                                                                                                          $last_name
 * @property string|null                                                                                                     $suffix
 * @property string                                                                                                          $address
 * @property string                                                                                                          $address2
 * @property string                                                                                                          $city
 * @property string                                                                                                          $state
 * @property string                                                                                                          $zip
 * @property string|null                                                                                                     $timezone
 * @property string                                                                                                          $status
 * @property int                                                                                                             $access_disabled
 * @property int|null                                                                                                        $is_auto_generated
 * @property string|null                                                                                                     $remember_token
 * @property \Illuminate\Support\Carbon|null                                                                                 $created_at
 * @property \Illuminate\Support\Carbon|null                                                                                 $updated_at
 * @property \Illuminate\Support\Carbon|null                                                                                 $deleted_at
 * @property string|null                                                                                                     $last_login
 * @property int                                                                                                             $is_online
 * @property string|null                                                                                                     $last_session_id
 * @property \Illuminate\Database\Eloquent\Collection|\Laravel\Nova\Actions\ActionEvent[]                                    $actions
 * @property int|null                                                                                                        $actions_count
 * @property \CircleLinkHealth\TimeTracking\Entities\Activity[]|\Illuminate\Database\Eloquent\Collection                     $activities
 * @property int|null                                                                                                        $activities_count
 * @property \CircleLinkHealth\TimeTracking\Entities\Activity[]|\Illuminate\Database\Eloquent\Collection                     $activitiesAsProvider
 * @property int|null                                                                                                        $activities_as_provider_count
 * @property \CircleLinkHealth\Customer\Entities\Appointment[]|\Illuminate\Database\Eloquent\Collection                      $appointments
 * @property int|null                                                                                                        $appointments_count
 * @property \CircleLinkHealth\TwoFA\Entities\AuthyUser                                                                      $authyUser
 * @property \CircleLinkHealth\Customer\Entities\CareAmbassador                                                              $careAmbassador
 * @property \CircleLinkHealth\SharedModels\Entities\CarePlan                                                                $carePlan
 * @property \App\CareplanAssessment                                                                                         $carePlanAssessment
 * @property \CircleLinkHealth\Customer\Entities\CarePerson[]|\Illuminate\Database\Eloquent\Collection                       $careTeamMembers
 * @property int|null                                                                                                        $care_team_members_count
 * @property \CircleLinkHealth\SharedModels\Entities\Allergy[]|\Illuminate\Database\Eloquent\Collection                      $ccdAllergies
 * @property int|null                                                                                                        $ccd_allergies_count
 * @property \CircleLinkHealth\SharedModels\Entities\CcdInsurancePolicy[]|\Illuminate\Database\Eloquent\Collection           $ccdInsurancePolicies
 * @property int|null                                                                                                        $ccd_insurance_policies_count
 * @property \CircleLinkHealth\SharedModels\Entities\Medication[]|\Illuminate\Database\Eloquent\Collection                   $ccdMedications
 * @property int|null                                                                                                        $ccd_medications_count
 * @property \CircleLinkHealth\SharedModels\Entities\Problem[]|\Illuminate\Database\Eloquent\Collection                      $ccdProblems
 * @property int|null                                                                                                        $ccd_problems_count
 * @property \CircleLinkHealth\SharedModels\Entities\Ccda[]|\Illuminate\Database\Eloquent\Collection                         $ccdas
 * @property int|null                                                                                                        $ccdas_count
 * @property \CircleLinkHealth\Customer\Entities\ChargeableService[]|\Illuminate\Database\Eloquent\Collection                $chargeableServices
 * @property int|null                                                                                                        $chargeable_services_count
 * @property \Illuminate\Database\Eloquent\Collection|\Laravel\Passport\Client[]                                             $clients
 * @property int|null                                                                                                        $clients_count
 * @property \CircleLinkHealth\Customer\Entities\Location[]|\Illuminate\Database\Eloquent\Collection                         $clinicalEmergencyContactLocations
 * @property int|null                                                                                                        $clinical_emergency_contact_locations_count
 * @property \App\Comment[]|\Illuminate\Database\Eloquent\Collection                                                         $comment
 * @property int|null                                                                                                        $comment_count
 * @property \CircleLinkHealth\SharedModels\Entities\CpmBiometric[]|\Illuminate\Database\Eloquent\Collection                 $cpmBiometrics
 * @property int|null                                                                                                        $cpm_biometrics_count
 * @property \CircleLinkHealth\SharedModels\Entities\CpmBloodPressure                                                        $cpmBloodPressure
 * @property \CircleLinkHealth\SharedModels\Entities\CpmBloodSugar                                                           $cpmBloodSugar
 * @property \CircleLinkHealth\SharedModels\Entities\CpmLifestyle[]|\Illuminate\Database\Eloquent\Collection                 $cpmLifestyles
 * @property int|null                                                                                                        $cpm_lifestyles_count
 * @property \CircleLinkHealth\SharedModels\Entities\CpmMedicationGroup[]|\Illuminate\Database\Eloquent\Collection           $cpmMedicationGroups
 * @property int|null                                                                                                        $cpm_medication_groups_count
 * @property \CircleLinkHealth\SharedModels\Entities\CpmMiscUser[]|\Illuminate\Database\Eloquent\Collection                  $cpmMiscUserPivot
 * @property int|null                                                                                                        $cpm_misc_user_pivot_count
 * @property \CircleLinkHealth\SharedModels\Entities\CpmMisc[]|\Illuminate\Database\Eloquent\Collection                      $cpmMiscs
 * @property int|null                                                                                                        $cpm_miscs_count
 * @property \CircleLinkHealth\SharedModels\Entities\CpmProblem[]|\Illuminate\Database\Eloquent\Collection                   $cpmProblems
 * @property int|null                                                                                                        $cpm_problems_count
 * @property \CircleLinkHealth\SharedModels\Entities\CpmSmoking                                                              $cpmSmoking
 * @property \CircleLinkHealth\SharedModels\Entities\CpmSymptom[]|\Illuminate\Database\Eloquent\Collection                   $cpmSymptoms
 * @property int|null                                                                                                        $cpm_symptoms_count
 * @property \CircleLinkHealth\SharedModels\Entities\CpmWeight                                                               $cpmWeight
 * @property \CircleLinkHealth\NurseInvoices\Entities\Dispute[]|\Illuminate\Database\Eloquent\Collection                     $disputes
 * @property int|null                                                                                                        $disputes_count
 * @property \CircleLinkHealth\Eligibility\Entities\TargetPatient                                                            $ehrInfo
 * @property \CircleLinkHealth\Customer\Entities\EhrReportWriterInfo                                                         $ehrReportWriterInfo
 * @property \App\Models\EmailSettings                                                                                       $emailSettings
 * @property \CircleLinkHealth\Customer\Entities\EmrDirectAddress[]|\Illuminate\Database\Eloquent\Collection                 $emrDirect
 * @property int|null                                                                                                        $emr_direct_count
 * @property \App\ForeignId[]|\Illuminate\Database\Eloquent\Collection                                                       $foreignId
 * @property int|null                                                                                                        $foreign_id_count
 * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection                             $forwardAlertsTo
 * @property int|null                                                                                                        $forward_alerts_to_count
 * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection                             $forwardedAlertsBy
 * @property int|null                                                                                                        $forwarded_alerts_by_count
 * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection                             $forwardedCarePlanApprovalEmailsBy
 * @property int|null                                                                                                        $forwarded_care_plan_approval_emails_by_count
 * @property mixed                                                                                                           $emr_direct_address
 * @property mixed                                                                                                           $full_name_with_id
 * @property string                                                                                                          $name
 * @property mixed                                                                                                           $timezone_abbr
 * @property mixed                                                                                                           $timezone_offset
 * @property mixed                                                                                                           $timezone_offset_hours
 * @property \App\Call[]|\Illuminate\Database\Eloquent\Collection                                                            $inboundActivities
 * @property int|null                                                                                                        $inbound_activities_count
 * @property \App\Call[]|\Illuminate\Database\Eloquent\Collection                                                            $inboundCalls
 * @property int|null                                                                                                        $inbound_calls_count
 * @property \App\Message[]|\Illuminate\Database\Eloquent\Collection                                                         $inboundMessages
 * @property int|null                                                                                                        $inbound_messages_count
 * @property \CircleLinkHealth\Customer\Entities\Location[]|\Illuminate\Database\Eloquent\Collection                         $locations
 * @property int|null                                                                                                        $locations_count
 * @property \CircleLinkHealth\Customer\Entities\Media[]|\Illuminate\Database\Eloquent\Collection                            $media
 * @property int|null                                                                                                        $media_count
 * @property \App\Note[]|\Illuminate\Database\Eloquent\Collection                                                            $notes
 * @property int|null                                                                                                        $notes_count
 * @property \CircleLinkHealth\Core\Entities\DatabaseNotification[]|\Illuminate\Notifications\DatabaseNotificationCollection $notifications
 * @property int|null                                                                                                        $notifications_count
 * @property \CircleLinkHealth\NurseInvoices\Entities\NurseInvoiceExtra[]|\Illuminate\Database\Eloquent\Collection           $nurseBonuses
 * @property int|null                                                                                                        $nurse_bonuses_count
 * @property \CircleLinkHealth\Customer\Entities\Nurse                                                                       $nurseInfo
 * @property \App\Observation[]|\Illuminate\Database\Eloquent\Collection                                                     $observations
 * @property int|null                                                                                                        $observations_count
 * @property \App\Call[]|\Illuminate\Database\Eloquent\Collection                                                            $outboundCalls
 * @property int|null                                                                                                        $outbound_calls_count
 * @property \App\Message[]|\Illuminate\Database\Eloquent\Collection                                                         $outboundMessages
 * @property int|null                                                                                                        $outbound_messages_count
 * @property \CircleLinkHealth\TimeTracking\Entities\PageTimer[]|\Illuminate\Database\Eloquent\Collection                    $pageTimersAsProvider
 * @property int|null                                                                                                        $page_timers_as_provider_count
 * @property \CircleLinkHealth\Customer\Entities\UserPasswordsHistory                                                        $passwordsHistory
 * @property \CircleLinkHealth\Customer\Entities\PatientAWVSummary[]|\Illuminate\Database\Eloquent\Collection                $patientAWVSummaries
 * @property int|null                                                                                                        $patient_a_w_v_summaries_count
 * @property \CircleLinkHealth\TimeTracking\Entities\Activity[]|\Illuminate\Database\Eloquent\Collection                     $patientActivities
 * @property int|null                                                                                                        $patient_activities_count
 * @property \CircleLinkHealth\Customer\Entities\Patient                                                                     $patientInfo
 * @property \CircleLinkHealth\Customer\Entities\PatientNurse                                                                $patientNurseAsPatient
 * @property \CircleLinkHealth\Customer\Entities\PatientMonthlySummary[]|\Illuminate\Database\Eloquent\Collection            $patientSummaries
 * @property int|null                                                                                                        $patient_summaries_count
 * @property \CircleLinkHealth\Customer\Entities\Permission[]|\Illuminate\Database\Eloquent\Collection                       $perms
 * @property int|null                                                                                                        $perms_count
 * @property \CircleLinkHealth\Customer\Entities\PhoneNumber[]|\Illuminate\Database\Eloquent\Collection                      $phoneNumbers
 * @property int|null                                                                                                        $phone_numbers_count
 * @property \CircleLinkHealth\Customer\Entities\Practice                                                                    $primaryPractice
 * @property \CircleLinkHealth\Customer\Entities\ProviderInfo                                                                $providerInfo
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection                     $revisionHistory
 * @property int|null                                                                                                        $revision_history_count
 * @property \CircleLinkHealth\Customer\Entities\Role[]|\Illuminate\Database\Eloquent\Collection                             $roles
 * @property int|null                                                                                                        $roles_count
 * @property \CircleLinkHealth\Customer\Entities\SaasAccount|null                                                            $saasAccount
 * @property \Illuminate\Database\Eloquent\Collection|\Laravel\Passport\Token[]                                              $tokens
 * @property int|null                                                                                                        $tokens_count
 * @property \App\CPRulesUCP[]|\Illuminate\Database\Eloquent\Collection                                                      $ucp
 * @property int|null                                                                                                        $ucp_count
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User careCoaches()
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User exceptType($type)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User filter(\App\Filters\QueryFilters $filters)
 * @method   static                                                                                                          bool|null forceDelete()
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User hasBillingProvider($billing_provider_id)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User intersectLocationsWith($user)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User intersectPracticesWith(\CircleLinkHealth\Customer\Entities\User $user, $withDemo = true)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User isBhiChargeable()
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User isBhiEligible()
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User isNotDemo()
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User withDownloadableInvoices($startDate, $endDate)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User newModelQuery()
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User newQuery()
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User notOfPracticeRequiringSpecialBhiConsent()
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User ofActiveBillablePractice()
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User ofPractice($practiceId)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User ofPracticeRequiringSpecialBhiConsent()
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User ofType($type, $excludeAwv = true)
 * @method   static                                                                                                          \Illuminate\Database\Query\Builder|\CircleLinkHealth\Customer\Entities\User onlyTrashed()
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User practiceStaff()
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User practicesWhereHasRoles($roleIds, $onlyActive = false)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User query()
 * @method   static                                                                                                          bool|null restore()
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User whereAccessDisabled($value)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User whereAddress($value)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User whereAddress2($value)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User whereAutoAttachPrograms($value)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User whereCity($value)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User whereCountCcmTime($value)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User whereCreatedAt($value)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User whereDeletedAt($value)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User whereDisplayName($value)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User whereEmail($value)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User whereFirstName($value)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User whereId($value)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User whereIsAutoGenerated($value)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User whereIsOnline($value)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User whereLastLogin($value)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User whereLastName($value)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User whereLastSessionId($value)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User wherePassword($value)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User whereProgramId($value)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User whereRememberToken($value)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User whereSaasAccountId($value)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User whereSkipBrowserChecks($value)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User whereState($value)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User whereStatus($value)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User whereSuffix($value)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User whereTimezone($value)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User whereUpdatedAt($value)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User whereUserRegistered($value)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User whereUserStatus($value)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User whereUsername($value)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User whereZip($value)
 * @method   static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User withCareTeamOfType($type)
 * @method   static                                                                                                          \Illuminate\Database\Query\Builder|\CircleLinkHealth\Customer\Entities\User withTrashed()
 * @method   static                                                                                                          \Illuminate\Database\Query\Builder|\CircleLinkHealth\Customer\Entities\User withoutTrashed()
 * @mixin \Eloquent
 * @property \CircleLinkHealth\Customer\EnrollableInvitationLink\EnrollableInvitationLink|null                       $enrollmentInvitationLinks
 * @property \CircleLinkHealth\Customer\EnrollableRequestInfo\EnrollableRequestInfo|null                             $enrollableInfoRequest
 * @property \App\LoginLogout[]|\Illuminate\Database\Eloquent\Collection                                             $loginEvents
 * @property int|null                                                                                                $login_events_count
 * @property \CircleLinkHealth\Eligibility\Entities\Enrollee|null                                                    $enrollee
 * @property int|null                                                                                                $enrollment_invitation_links_count
 * @method   static                                                                                                  \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User hasSelfEnrollmentInvite()
 * @method   static                                                                                                  \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User hasSelfEnrollmentInviteReminder(\Carbon\Carbon $date = null, $has = true)
 * @method   static                                                                                                  \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User haveEnrollableInvitationDontHaveReminder(\Carbon\Carbon $dateInviteSent = null)
 * @method   static                                                                                                  \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User patientsPendingCLHApproval(\CircleLinkHealth\Customer\Entities\User $approver)
 * @method   static                                                                                                  \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User patientsPendingProviderApproval(\CircleLinkHealth\Customer\Entities\User $approver)
 * @property \CircleLinkHealth\Eligibility\Entities\Enrollee[]|\Illuminate\Database\Eloquent\Collection              $assignedEnrollees
 * @property int|null                                                                                                $assigned_enrollees_count
 * @property \CircleLinkHealth\Customer\Entities\PatientCcmStatusRevision[]|\Illuminate\Database\Eloquent\Collection $patientCcmStatusRevisions
 * @property int|null                                                                                                $patient_ccm_status_revisions_count
 * @property string|null                                                                                             $scope
 * @property AttestedProblem[]|EloquentCollection                                                                    $attestedProblems
 * @property int|null                                                                                                $attested_problems_count
 * @property ChargeablePatientMonthlySummary[]|EloquentCollection                                                    $chargeableMonthlySummaries
 * @property int|null                                                                                                $chargeable_monthly_summaries_count
 * @property EloquentCollection|EndOfMonthCcmStatusLog[]                                                             $endOfMonthCcmStatusLog
 * @property int|null                                                                                                $end_of_month_ccm_status_log_count
 */
class User extends BaseModel implements AuthenticatableContract, CanResetPasswordContract, HasMedia
{
    use \Laravel\Nova\Actions\Actionable;
    use Authenticatable;
    use CanResetPassword;
    use CerberusSiteUserTrait;
    use Filterable;
    use HasApiTokens;
    use HasEmrDirectAddress;
    use HasMediaTrait;
    use Impersonate;
    use MakesOrReceivesCalls;
    use Notifiable;
    use PivotEventTrait;
    use SaasAccountable;
    use Searchable;
    use SelfEnrollableTrait;
    use SoftDeletes;
    use TimezoneTrait;

    const FORWARD_ALERTS_IN_ADDITION_TO_PROVIDER                   = 'forward_alerts_in_addition_to_provider';
    const FORWARD_ALERTS_INSTEAD_OF_PROVIDER                       = 'forward_alerts_instead_of_provider';
    const FORWARD_CAREPLAN_APPROVAL_EMAILS_IN_ADDITION_TO_PROVIDER = 'forward_careplan_approval_emails_in_addition_to_provider';
    const FORWARD_CAREPLAN_APPROVAL_EMAILS_INSTEAD_OF_PROVIDER     = 'forward_careplan_approval_emails_instead_of_provider';

    const SCOPE_LOCATION = 'location';
    const SCOPE_PRACTICE = 'practice';

    const SURVEY_ONLY = 'survey-only';
    /**
     * Package Clockwork is hardcoded to look for $user->name. Adding this so that it will work.
     *
     * @var string|null
     */
    public $name;
    public $phi = [
        'username',
        'email',
        'display_name',
        'first_name',
        'last_name',
        'suffix',
        'address',
        'address2',
        'city',
        'state',
        'zip',
    ];

    protected $attributes = [
        'timezone' => 'America/New_York',
    ];

    protected $dates = ['user_registered'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'scope',
        'saas_account_id',
        'skip_browser_checks',
        'username',
        'password',
        'email',
        'user_url',
        'user_registered',
        'user_activation_log',
        'user_status',
        'auto_attach_programs',
        'display_name',
        'spam',
        'first_name',
        'last_name',
        'suffix',
        'address',
        'address2',
        'city',
        'state',
        'zip',
        'timezone',
        'is_auto_generated',
        'program_id',
        'remember_token',
        'last_login',
        'last_session_id',
        'is_online',
    ];

    protected $hidden = [
        //@todo: Need to fix repository package. It does not validate hidden attributes. May temporarily comment out until then
        'password',
    ];

    protected $rules = [];

    //we need this for ProtectsPHI.php
    //it is used with CerberusSiteUserTrait::setRelation
    protected $with = ['roles', 'perms'];

    private static $canSeePhi = [];

    private $isAllowedToSeePhi;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->rules = [
            'username'              => 'required',
            'email'                 => 'required|email|unique:users,email',
            'password'              => ['required', 'filled', 'min:8', new PasswordCharacters()],
            'password_confirmation' => 'required|same:password',
        ];
    }

    public function activities()
    {
        return $this->hasMany(\CircleLinkHealth\TimeTracking\Entities\Activity::class, 'patient_id');
    }

    public function activitiesAsProvider()
    {
        return $this->hasMany(\CircleLinkHealth\TimeTracking\Entities\Activity::class, 'provider_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'patient_id');
    }

    public function assignedEnrollees()
    {
        return $this->hasMany(Enrollee::class, 'care_ambassador_user_id');
    }

    /**
     * Assigns calls to a nurse.
     *
     * @param mixed $calls
     */
    public function assignOutboundCalls($calls)
    {
        $calls = Call::whereIn('id', parseIds($calls))->get();

        return $this->outboundCalls()->saveMany($calls);
    }

    /**
     * Attach Role to User.
     * Returns false if Role was already attached, and true if it was attached now.
     *
     * @param $roleId
     *
     * @return bool
     */
    public function attachGlobalRole($roleId)
    {
        if (is_array($roleId)) {
            foreach ($roleId as $key => $role) {
                if (1 === count($roleId)) {
                    return $this->attachGlobalRole($role);
                }

                $this->attachGlobalRole($role);
                unset($roleId[$key]);
            }
        }

        if (is_object($roleId)) {
            $roleId = $roleId->id;
        }

        try {
            //Attach the role
            $this->roles()->attach($roleId);
        } catch (\Exception $e) {
            if ($e instanceof QueryException) {
                $errorCode = $e->errorInfo[1];
                if (1062 == $errorCode) {
                    //return false so we know nothing was attached
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Attach Location(s).
     *
     * @param $location |array
     */
    public function attachLocation($location)
    {
        if (is_a($location, Collection::class) || is_a($location, EloquentCollection::class)) {
            $location = $location->all();
        }

        if (is_array($location)) {
            foreach ($location as $key => $loc) {
                if (1 === count($location)) {
                    return $this->attachLocation($loc);
                }

                $this->attachLocation($loc);
                unset($location[$key]);
            }
        }

        if (is_a($location, Location::class)) {
            $location = $location->id;
        }

        if (empty($location)) {
            return;
        }

        if ( ! $this->locations()->where('locations.id', $location)->exists()) {
            $this->locations()->attach($location);
        }
    }

    public function attachPractice($practice, array $roleIds, $sendBillingReports = null)
    {
        $ids = parseIds($practice);

        if ( ! array_key_exists(0, $ids)) {
            throw new InvalidArgumentException('Could not parse a Practice id from the argument provided.');
        }

        $practiceId = $ids[0];

        $rolesForPractice = PracticeRoleUser::where('user_id', '=', $this->id)
            ->where('program_id', '=', $practiceId)
            ->get();

        //remove any roles not in $roleIds array
        foreach ($rolesForPractice as $roleForPractice) {
            //sometimes role_id is null
            if ( ! $roleForPractice->role_id) {
                continue;
            }

            if ( ! in_array($roleForPractice->role_id, $roleIds)) {
                //table does not have primary key, so need raw query
                $tableName = (new PracticeRoleUser())->getTable();
                $q         = "DELETE FROM $tableName where user_id = ? and program_id = ? and role_id = ?";
                \DB::delete($q, [$roleForPractice->user_id, $roleForPractice->program_id, $roleForPractice->role_id]);
            }
        }

        if (empty($roleIds)) {
            PracticeRoleUser::updateOrCreate(
                [
                    'user_id'    => $this->id,
                    'program_id' => $practiceId,
                ],
                null != $sendBillingReports
                    ? [
                        'send_billing_reports' => $sendBillingReports,
                    ]
                    : []
            );
        } else {
            foreach ($roleIds as $r) {
                PracticeRoleUser::updateOrCreate(
                    [
                        'user_id'    => $this->id,
                        'program_id' => $practiceId,
                        'role_id'    => $r,
                    ],
                    null != $sendBillingReports
                        ? [
                            'send_billing_reports' => $sendBillingReports,
                        ]
                        : []
                );
            }
        }
    }

    /**
     * Attach Role(s) to a User for a specific practice.
     *
     * @param $roles
     * @param $practiceId
     *
     * @return bool
     */
    public function attachRoleForPractice($roles, $practiceId)
    {
        try {
            $this->attachRoleForSite($roles, $practiceId);

            return true;
        } catch (\Exception $e) {
            //check if this is a mysql exception for unique key constraint
            if ($e instanceof \Illuminate\Database\QueryException) {
                $errorCode = $e->errorInfo[1];
                if (1062 == $errorCode) {
                    //do nothing
                    //we don't actually want to terminate the program if we detect duplicates
                    //we just don't wanna add the row again
                }
            }
        }

        return false;
    }

    public function attestedProblems()
    {
        return $this->hasMany(AttestedProblem::class, 'patient_user_id');
    }

    public function authyUser()
    {
        return $this->hasOne(AuthyUser::class);
    }

    public function autocomplete()
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name() ?? $this->display_name,
            'program_id' => $this->program_id,
        ];
    }

    public function billableProblems()
    {
        $genericDiabetes = \CircleLinkHealth\SharedModels\Entities\CpmProblem::where('name', 'Diabetes')->firstOrFail();

        return $this->ccdProblems()
            ->whereNotNull('cpm_problem_id')
            ->when($genericDiabetes, function ($p) use ($genericDiabetes) {
                $p->where('cpm_problem_id', '!=', $genericDiabetes->id);
            })
            ->with(['cpmProblem', 'icd10Codes'])
            ->where('is_monitored', true);
    }

    public function billingCodes(Carbon $monthYear)
    {
        $summary = $this->patientSummaries()
            ->where('month_year', $monthYear->toDateString())
            ->with('chargeableServices')
            ->has('chargeableServices')
            ->first();

        if ( ! $summary) {
            return '';
        }

        return $summary->chargeableServices
            ->implode('code', ', ');
    }

    /**
     * Get billing provider.
     *
     * @return User
     */
    public function billingProvider()
    {
        return $this->careTeamMembers()->where('type', '=', CarePerson::BILLING_PROVIDER);
    }

    /**
     * Get billing provider User.
     */
    public function billingProviderUser(): ?User
    {
        return $this->billingProvider->isEmpty()
            ? null
            : optional($this->billingProvider->first())->user;
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(
            function ($user) {
                if ($user->isProvider()) {
                    $user->providerInfo()->delete();
                }

                if ($user->isParticipant()) {
                    $user->carePlan()->delete();
                    $user->careTeamMembers()->delete();
                    $user->patientInfo()->delete();
                    $user->patientSummaries()->delete();
                    $user->inboundScheduledCalls()->delete();
                }

                $user->loginEvents()->delete();
            }
        );

        static::restoring(
            function ($user) {
                if ($user->isProvider()) {
                    $user->providerInfo()->restore();
                }

                if ($user->isParticipant()) {
                    $user->patientInfo()->restore();
                    $user->carePlan()->restore();
                    $user->careTeamMembers()->get()->each(function ($ctm) {
                        $ctm->restore();
                    });
                }
            }
        );

        static::pivotAttached(function ($user, $relationName, $pivotIds, $pivotIdsAttributes) {
            if ('roles' === $relationName) {
                $user->clearRolesCache();
            }
        });

        static::pivotDetached(function ($user, $relationName, $pivotIds) {
            if ('roles' === $relationName) {
                $user->clearRolesCache();
            }
        });

        static::pivotUpdated(function ($user, $relationName, $pivotIds, $pivotIdsAttributes) {
            if ('roles' === $relationName) {
                $user->clearRolesCache();
            }
        });

        static::updating(function ($model) {
            //this is how we catch standard eloquent events
        });
    }

    public function cachedNotificationsList()
    {
        if (in_array(config('cache.default'), ['redis'])) {
            return new UserNotificationList($this->id);
        }

        return new EmptyUserNotificationList();
    }

    public function calls()
    {
        return $this->outboundCalls();
    }

    public function canApproveCarePlans()
    {
        return $this->hasPermissionForSite('care-plan-approve', $this->getPrimaryPracticeId());
    }

    /**
     * @return bool
     */
    public function canImpersonate()
    {
        return $this->isAdmin();
    }

    public function canQAApproveCarePlans()
    {
        return $this->hasPermissionForSite('care-plan-qa-approve', $this->getPrimaryPracticeId());
    }

    public function canRNApproveCarePlans()
    {
        return $this->hasPermissionForSite('care-plan-rn-approve', $this->getPrimaryPracticeId());
    }

    /**
     * This function is called for every record of a model.
     * It is very important to cache at all levels:
     * - Roles (cerberus)
     * - Permissions (cerberus)
     * - User Model (here)
     * - ProtectsPHI trait (see ProtectsPHI.php).
     *
     * @return mixed
     */
    public function canSeePhi()
    {
        if ( ! isset(self::$canSeePhi[$this->id])) {
            self::$canSeePhi[$this->id] = $this->hasPermission('phi.read');
        }

        return self::$canSeePhi[$this->id];
    }

    public function careAmbassador()
    {
        return $this->hasOne(CareAmbassador::class);
    }

    public function carePlan()
    {
        return $this->hasOne(CarePlan::class, 'user_id', 'id');
    }

    /**
     * Temporary solution for `careplan_assessments.careplan_id` not being an actual `careplan_id` but a `user_id`.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     *
     * @see CPM-423
     */
    public function carePlanAssessment()
    {
        return $this->hasOne(CareplanAssessment::class, 'careplan_id');
    }

    public function careTeamMembers()
    {
        return $this->hasMany(CarePerson::class, 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function ccdAllergies()
    {
        return $this->hasMany(Allergy::class, 'patient_id');
    }

    public function ccdas()
    {
        return $this->hasMany(Ccda::class, 'patient_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function ccdInsurancePolicies()
    {
        return $this->hasMany(CcdInsurancePolicy::class, 'patient_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ccdMedications()
    {
        return $this->hasMany(Medication::class, 'patient_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ccdProblems()
    {
        return $this->hasMany(Problem::class, 'patient_id');
    }

    public function chargeableMonthlySummaries()
    {
        return $this->hasMany(ChargeablePatientMonthlySummary::class, 'patient_user_id');
    }

    public function chargeableServices()
    {
        return $this->morphToMany(ChargeableService::class, 'chargeable')
            ->withPivot(['amount'])
            ->withTimestamps();
    }

    /**
     * Delete all existing Phone Numbers and replace them with a new primary number.
     *
     * @param $number
     * @param $type
     * @param bool       $isPrimary
     * @param mixed|null $extension
     *
     * @return bool
     */
    public function clearAllPhonesAndAddNewPrimary(
        $number,
        $type,
        $isPrimary = false,
        $extension = null
    ) {
        $this->phoneNumbers()->delete();

        if (empty($number)) {
            //assume we wanted to delete the phone(s)
            return true;
        }

        return $this->phoneNumbers()->create(
            [
                'number'     => (new \CircleLinkHealth\Core\StringManipulation())->formatPhoneNumber($number),
                'type'       => PhoneNumber::getTypes()[$type] ?? null,
                'is_primary' => $isPrimary,
                'extension'  => $extension,
            ]
        );
    }

    public function clinicalEmergencyContactLocations()
    {
        return $this->morphedByMany(Location::class, 'contactable', 'contacts')
            ->withPivot('name')
            ->wherePivot('name', '=', 'in_addition_to_billing_provider')
            ->orWherePivot('name', '=', 'instead_of_billing_provider')
            ->withTimestamps();
    }

    public function comment()
    {
        return $this->hasMany('App\Comment', 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cpmBiometrics()
    {
        return $this->belongsToMany(CpmBiometric::class, 'cpm_biometrics_users', 'patient_id')
            ->withPivot('cpm_instruction_id')
            ->withTimestamps('created_at', 'updated_at');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cpmBloodPressure()
    {
        return $this->hasOne(CpmBloodPressure::class, 'patient_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cpmBloodSugar()
    {
        return $this->hasOne(CpmBloodSugar::class, 'patient_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cpmLifestyles()
    {
        return $this->belongsToMany(CpmLifestyle::class, 'cpm_lifestyles_users', 'patient_id')
            ->withPivot('cpm_instruction_id')
            ->withTimestamps('created_at', 'updated_at');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cpmMedicationGroups()
    {
        return $this->belongsToMany(CpmMedicationGroup::class, 'cpm_medication_groups_users', 'patient_id')
            ->withPivot('cpm_instruction_id')
            ->withTimestamps('created_at', 'updated_at');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cpmMiscs()
    {
        return $this->belongsToMany(CpmMisc::class, 'cpm_miscs_users', 'patient_id')
            ->withPivot('cpm_instruction_id')
            ->withTimestamps('created_at', 'updated_at');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cpmMiscUserPivot()
    {
        return $this->hasMany(CpmMiscUser::class, 'patient_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cpmProblems()
    {
        return $this->belongsToMany(CpmProblem::class, 'cpm_problems_users', 'patient_id')
            ->withPivot('cpm_instruction_id')
            ->withTimestamps('created_at', 'updated_at');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cpmSmoking()
    {
        return $this->hasOne(CpmSmoking::class, 'patient_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cpmSymptoms()
    {
        return $this->belongsToMany(CpmSymptom::class, 'cpm_symptoms_users', 'patient_id')
            ->withPivot('cpm_instruction_id')
            ->withTimestamps('created_at', 'updated_at');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cpmWeight()
    {
        return $this->hasOne(CpmWeight::class, 'patient_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function disputes()
    {
        return $this->hasMany(Dispute::class);
    }

    /**
     * (functions as an @ehrKeychain).
     *
     * Relates to TargetPatient class, contains all patient info for EHR
     * (ehr_practice_id, ehr_department_id etc)
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ehrInfo()
    {
        return $this->hasOne(TargetPatient::class);
    }

    public function ehrReportWriterInfo()
    {
        return $this->hasOne(EhrReportWriterInfo::class, 'user_id', 'id');
    }

    public function emailSettings()
    {
        return $this->hasOne(EmailSettings::class);
    }

    public function endOfMonthCcmStatusLog()
    {
        return $this->hasMany(EndOfMonthCcmStatusLog::class, 'patient_user_id');
    }

    public function enrollee()
    {
        return $this->hasOne(Enrollee::class, 'user_id');
    }

    public function firstOrNewProviderInfo()
    {
        if ( ! $this->isProvider()) {
            return false;
        }

        return ProviderInfo::firstOrCreate(
            [
                'user_id' => $this->id,
            ]
        );
    }

    public function foreignId()
    {
        return $this->hasMany(ForeignId::class);
    }

    public function formattedBhiTime()
    {
        $seconds = $this->getBhiTime();

        return $this->formattedTime($seconds);
    }

    public function formattedCcmTime()
    {
        $seconds = $this->getCcmTime();

        return $this->formattedTime($seconds);
    }

    public function formattedTime($seconds)
    {
        $H = floor($seconds / 3600);
        $i = ($seconds / 60) % 60;
        $s = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $H, $i, $s);
    }

    /**
     * Forward Alerts to another User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function forwardAlertsTo()
    {
        return $this->morphToMany(User::class, 'contactable', 'contacts')
            ->withPivot('name')
            ->withTimestamps();
    }

    /**
     * Get the Users that are forwarding alerts to this User.
     * Inverse Relationship of forwardAlertsTo().
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function forwardedAlertsBy()
    {
        return $this->morphedByMany(User::class, 'contactable', 'contacts')
            ->withPivot('name')
            ->wherePivot('name', '=', User::FORWARD_ALERTS_IN_ADDITION_TO_PROVIDER)
            ->orWherePivot('name', '=', User::FORWARD_ALERTS_INSTEAD_OF_PROVIDER)
            ->withTimestamps();
    }

    /**
     * Get the Users that are forwarding alerts to this User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function forwardedCarePlanApprovalEmailsBy()
    {
        return $this->forwardedAlertsBy()
            ->withPivot('name')
            ->wherePivot('name', '=', User::FORWARD_CAREPLAN_APPROVAL_EMAILS_IN_ADDITION_TO_PROVIDER)
            ->orWherePivot('name', '=', User::FORWARD_CAREPLAN_APPROVAL_EMAILS_INSTEAD_OF_PROVIDER)
            ->withTimestamps();
    }

    /**
     * Forward Alerts/Notifications to another User.
     * Attaches forwards to a user using forwardAlertsTo() relationship.
     *
     * @param mixed $receiverUserId
     * @param mixed $forwardTypeName
     */
    public function forwardTo($receiverUserId, $forwardTypeName)
    {
        $this->forwardAlertsTo()->attach(
            $receiverUserId,
            [
                'name' => $forwardTypeName,
            ]
        );
    }

    public function getActiveDate()
    {
        if ( ! $this->patientInfo) {
            return '';
        }

        return $this->patientInfo->active_date;
    }

    public function getAge()
    {
        $from = new DateTime($this->getBirthDate());
        $to   = new DateTime('today');

        return $from->diff($to)->y;
    }

    public function getAgentEmail()
    {
        if ( ! $this->patientInfo) {
            return '';
        }

        return $this->patientInfo->agent_email;
    }

    public function getAgentName()
    {
        if ( ! $this->patientInfo) {
            return '';
        }

        return $this->patientInfo->agent_name;
    }

    public function getAgentPhone()
    {
        if ( ! $this->patientInfo) {
            return '';
        }

        return $this->patientInfo->agent_telephone;
    }

    public function getAgentRelationship()
    {
        if ( ! $this->patientInfo) {
            return '';
        }

        return $this->patientInfo->agent_relationship;
    }

    public function getAgentTelephone()
    {
        return $this->getAgentPhone();
    }

    public function getBhiTime()
    {
        return optional(
            $this->patientSummaries()
                ->select(['bhi_time', 'id'])
                ->orderBy('id', 'desc')
                ->whereMonthYear(Carbon::now()->startOfMonth())
                ->first()
        )->bhi_time ?? 0;
    }

    public function getBillingProviderId()
    {
        $bp = '';
        if ($this->careTeamMembers->isEmpty()) {
            return $bp;
        }

        foreach ($this->careTeamMembers as $careTeamMember) {
            if ('billing_provider' == $careTeamMember->type) {
                $bp = $careTeamMember->member_user_id;
            }
        }

        return $bp;
    }

    /**
     * Get billing provider's full name.
     *
     * @return string
     */
    public function getBillingProviderName()
    {
        $billingProvider = $this->billingProviderUser();

        return $billingProvider
            ? $billingProvider->getFullName()
            : '';
    }

    /**
     * Get billing provider's phone.
     *
     * @return string
     */
    public function getBillingProviderPhone()
    {
        $billingProvider = $this->billingProviderUser();

        return $billingProvider
            ? $billingProvider->getPhone()
            : '';
    }

    public function getBirthDate()
    {
        if ( ! $this->patientInfo) {
            return '';
        }

        if ( ! is_null($this->patientInfo->birth_date)) {
            return $this->patientInfo->birth_date->toDateString();
        }

        return '';
    }

    public function getCareplanLastPrinted()
    {
        if ( ! $this->carePlan) {
            return '';
        }

        return $this->carePlan->last_printed;
    }

    public function getCareplanMode()
    {
        $careplanMode = null;

        if ($this->carePlan) {
            $careplanMode = $this->carePlan->mode;
        }

        if ( ! $careplanMode && $this->primaryPractice && $this->primaryPractice->settings->isNotEmpty()) {
            $careplanMode = $this->primaryPractice->cpmSettings()->careplan_mode;
        }

        if ( ! $careplanMode) {
            $careplanMode = CarePlan::WEB;
        }

        return $careplanMode;
    }

    public function getCarePlanProviderApprover()
    {
        if ( ! $this->carePlan) {
            return '';
        }

        return $this->carePlan->provider_approver_id;
    }

    public function getCarePlanProviderApproverDate()
    {
        if ( ! $this->carePlan) {
            return '';
        }

        return $this->carePlan->provider_date;
    }

    public function getCarePlanQAApprover()
    {
        if ( ! $this->carePlan) {
            return '';
        }

        return $this->carePlan->qa_approver_id;
    }

    public function getCarePlanQADate()
    {
        if ( ! $this->carePlan) {
            return '';
        }

        return $this->carePlan->qa_date;
    }

    public function getCarePlanStatus()
    {
        if ( ! $this->carePlan) {
            return '';
        }

        return $this->carePlan->status;
    }

    public function getCareTeam()
    {
        $ct              = [];
        $careTeamMembers = $this->careTeamMembers->where('type', 'member');
        if ($careTeamMembers->count() > 0) {
            foreach ($careTeamMembers as $careTeamMember) {
                $ct[] = $careTeamMember->member_user_id;
            }
        }

        return $ct;
    }

    /**
     * Get the CarePeople who have subscribed to receive alerts for this Patient.
     * Returns a Collection of User objects, or an Empty Collection.
     *
     * @return Collection
     */
    public function getCareTeamReceivesAlerts()
    {
        if ( ! $this->primaryPractice->send_alerts) {
            return new Collection();
        }

        $careTeam = $this->careTeamMembers->where('alert', '=', true)
            ->keyBy('member_user_id')
            ->unique()
            ->values();

        $users = new Collection();

        //Get email forwarding
        foreach ($careTeam as $carePerson) {
            $forwardsTo = optional($carePerson->user)->forwardAlertsTo;
            if ($forwardsTo) {
                $forwards = $forwardsTo->whereIn(
                    'pivot.name',
                    [
                        User::FORWARD_ALERTS_IN_ADDITION_TO_PROVIDER,
                        User::FORWARD_ALERTS_INSTEAD_OF_PROVIDER,
                    ]
                );

                if ($forwards->isEmpty() && $carePerson->user) {
                    $users->push($carePerson->user);
                }

                foreach ($forwards as $forwardee) {
                    if (User::FORWARD_ALERTS_IN_ADDITION_TO_PROVIDER == $forwardee->pivot->name) {
                        $users->push($carePerson->user);
                        $users->push($forwardee);
                    }

                    if (User::FORWARD_ALERTS_INSTEAD_OF_PROVIDER == $forwardee->pivot->name) {
                        $users->push($forwardee);
                    }
                }
            }
        }

        //Get clinical emergency contacts from locations
        foreach ($this->locations as $location) {
            if ( ! $location->clinicalEmergencyContact->isEmpty()) {
                $contact = $location->clinicalEmergencyContact->first();

                if (CarePerson::INSTEAD_OF_BILLING_PROVIDER == $contact->pivot->name) {
                    $users = new Collection();
                    $users->push($contact);

                    return $users;
                }

                $users->push($contact);
            }
        }

        return $users;
    }

    public function getCcmStatus()
    {
        if ( ! $this->patientInfo) {
            return '';
        }

        return $this->patientInfo->ccm_status;
    }

    public function getCcmTime()
    {
        return optional(
            $this->patientSummaries()
                ->select(['ccm_time', 'id'])
                ->orderBy('id', 'desc')
                ->whereMonthYear(Carbon::now()->startOfMonth())
                ->first()
        )->ccm_time ?? 0;
    }

    public function getConsentDate()
    {
        if ( ! $this->patientInfo) {
            return '';
        }

        return $this->patientInfo->consent_date;
    }

    public function getDailyReminderAreas()
    {
        if ( ! $this->patientInfo) {
            return '';
        }

        return $this->patientInfo->daily_reminder_areas;
    }

    public function getDailyReminderOptin()
    {
        if ( ! $this->patientInfo) {
            return '';
        }

        return $this->patientInfo->daily_reminder_optin;
    }

    public function getDailyReminderTime()
    {
        if ( ! $this->patientInfo) {
            return '';
        }

        return $this->patientInfo->daily_reminder_time;
    }

    public function getDatePaused()
    {
        if ( ! $this->patientInfo) {
            return '';
        }

        return $this->patientInfo->date_paused;
    }

    public function getDateUnreachable()
    {
        if ( ! $this->patientInfo) {
            return '';
        }

        return $this->patientInfo->date_unreachable;
    }

    public function getDateWithdrawn()
    {
        if ( ! $this->patientInfo) {
            return '';
        }

        return $this->patientInfo->date_withdrawn;
    }

    public function getDoctorFullNameWithSpecialty()
    {
        $specialty = '';

        if ($this->providerInfo) {
            $specialty = $this->getSpecialty() == $this->getSuffix()
                ? ''
                : "\n {$this->getSpecialty()}";
        }

        $fullName = $this->getFullName();
        $doctor   = Str::startsWith(strtolower($fullName), 'dr.')
            ? ''
            : 'Dr. ';

        return $doctor.$fullName.$specialty;
    }

    public function getEmailForPasswordReset()
    {
        return $this->email;
    }

    public function getFirstName()
    {
        return $this->first_name;
    }

    public function getFullName()
    {
        $firstName = $this->first_name;
        $lastName  = $this->last_name;
        $suffix    = $this->getSuffix();

        return trim("${firstName} ${lastName} ${suffix}");
    }

    public function getFullNameWithId()
    {
        $name = $this->getFullName();

        return $name.' ('.$this->id.')';
    }

    public function getFullNameWithIdAttribute()
    {
        $name = $this->getFullName();

        return $name.' ('.$this->id.')';
    }

    public function getGender()
    {
        if ( ! $this->patientInfo) {
            return '';
        }

        return $this->patientInfo->gender;
    }

    public function getHomePhoneNumber()
    {
        return $this->getPhone();
    }

    public function getHospitalReminderAreas()
    {
        if ( ! $this->patientInfo) {
            return '';
        }

        return $this->patientInfo->hospital_reminder_areas;
    }

    public function getHospitalReminderOptin()
    {
        if ( ! $this->patientInfo) {
            return '';
        }

        return $this->patientInfo->hospital_reminder_optin;
    }

    public function getHospitalReminderTime()
    {
        if ( ! $this->patientInfo) {
            return '';
        }

        return $this->patientInfo->hospital_reminder_time;
    }

    public function getLastName()
    {
        return $this->last_name;
    }

    public function getLeadContactID()
    {
        $lc = [];
        if ( ! $this->careTeamMembers) {
            return '';
        }
        if ($this->careTeamMembers->count() > 0) {
            foreach ($this->careTeamMembers as $careTeamMember) {
                if ('lead_contact' == $careTeamMember->type) {
                    $lc = $careTeamMember->member_user_id;
                }
            }
        }

        return $lc;
    }

    public function getLegacyBhiNursePatientCacheKey($patientId)
    {
        if ( ! $this->id) {
            throw new \Exception('User ID not found.');
        }

        return "hide_legacy_bhi_banner:$this->id:$patientId";
    }

    public function getMobilePhoneNumber()
    {
        if ( ! $this->phoneNumbers) {
            return '';
        }
        $phoneNumber = $this->phoneNumbers->where('type', 'mobile')->first();
        if ($phoneNumber) {
            return $phoneNumber->number;
        }

        return '';
    }

    public function getMRN()
    {
        if ( ! $this->patientInfo) {
            return '';
        }

        return $this->patientInfo->mrn_number;
    }

    public function getMrnNumber()
    {
        return $this->getMRN();
    }

    /**
     * Workaround for Nova Action core code. It calls user->name, but our user does not have a 'name' attribute.
     *
     * @return string
     */
    public function getNameAttribute()
    {
        return $this->display_name;
    }

    public function getNoteChannelsText()
    {
        $channels = $this->primaryPractice->cpmSettings()->notesChannels();
        $i        = 1;
        $last     = count($channels);
        $output   = '';

        foreach ($channels as $channel) {
            $output .= (1 == $i
                    ? ''
                    : ', ')
                .($i == $last && $i > 1
                    ? 'and '
                    : '').$channel;

            ++$i;
        }

        return $output;
    }

    public function getNotifiesText()
    {
        $careTeam = $this->getCareTeamReceivesAlerts();
        $i        = 1;
        $last     = $careTeam->count();
        $output   = '';

        foreach ($careTeam as $carePerson) {
            $output .= (1 == $i
                    ? ''
                    : ', ').($i == $last && $i > 1
                    ? 'and '
                    : '').$carePerson->getFullName();

            ++$i;
        }

        return $output;
    }

    public function getNpiNumber()
    {
        if ( ! $this->providerInfo) {
            return '';
        }

        return $this->providerInfo->npi_number;
    }

    // CCD Models

    public function getPatientRules()
    {
        return [
            'daily_reminder_optin'    => 'required',
            'daily_reminder_time'     => 'required',
            'daily_reminder_areas'    => 'required',
            'hospital_reminder_optin' => 'required',
            'hospital_reminder_time'  => 'required',
            'hospital_reminder_areas' => 'required',
            'first_name'              => 'required',
            'last_name'               => 'required',
            'gender'                  => 'required',
            'mrn_number'              => 'required',
            'birth_date'              => 'required',
            'home_phone_number'       => 'required',
            'consent_date'            => 'required',
            'ccm_status'              => 'required',
            'program_id'              => 'required',
            'email'                   => [
                'sometimes',
                Rule::unique('users', 'email')->ignore($this),
            ],
        ];
    }

    public function getPhone()
    {
        if ( ! $this->phoneNumbers) {
            return '';
        }

        $phoneNumbers = $this->phoneNumbers;
        $number       = null;

        if (1 == count($phoneNumbers)) {
            $number = $phoneNumbers->first()->number;
        } else {
            $primary = $phoneNumbers->where('is_primary', true)->first();
            if ($primary) {
                $number = $primary->number;
            } elseif (count($phoneNumbers) > 0) {
                $number = $phoneNumbers->first()->number;
            }
        }

        if ($number) {
            $number = $this->formatNumberForSms($number);
        }

        return $number ?? '';
    }

    public function getPhoneNumberForSms(): string
    {
        if ( ! $this->phoneNumbers) {
            return '';
        }

        $validCellNumbers = $this->phoneNumbers
            ->whereNotNull('number')->where('number', '!=', '')
            ->map(function ($phone) {
                // when running tests, faker doesn't always produce valid us mobile phone number
                $phoneNumber = \Propaganistas\LaravelPhone\PhoneNumber::make($phone->number, ['US', 'CY']);
                try {
                    //isOfType might throw, in that case we do not use the number
                    if ( ! isProductionEnv() || $phoneNumber->isOfType('mobile')) {
                        if ($this->isCypriotNumber($phoneNumber)) {
                            return $phone->number;
                        }

                        return formatPhoneNumberE164($phone->number);
                    }
                } catch (NumberParseException $e) {
                    Log::warning($e->getMessage());

                    return false;
                }

                return false;
            })
            ->filter()
            ->unique()
            ->values();

        return $validCellNumbers->first() ?? '';
    }

    public function getPreferredCcContactDays()
    {
        if ( ! $this->patientInfo) {
            return '';
        }

        return $this->patientInfo->preferred_cc_contact_days;
    }

    public function getPreferredContactLanguage()
    {
        if ( ! $this->patientInfo) {
            return '';
        }

        return $this->patientInfo->preferred_contact_language;
    }

    public function getPreferredContactLocation()
    {
        if ( ! $this->patientInfo) {
            return '';
        }

        return $this->patientInfo->preferred_contact_location;
    }

    public function getPreferredContactMethod()
    {
        if ( ! $this->patientInfo) {
            return '';
        }

        return $this->patientInfo->preferred_contact_method;
    }

    public function getPreferredContactTime()
    {
        if ( ! $this->patientInfo) {
            return '';
        }

        return $this->patientInfo->preferred_contact_time;
    }

    public function getPreferredLocationAddress()
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $locationId = $this->patientInfo->preferred_contact_location;
        if (empty($locationId)) {
            return false;
        }

        return Location::find($locationId);
    }

    public function getPreferredLocationName()
    {
        if ( ! $this->patientInfo) {
            return 'N/A';
        }

        return optional($this->patientInfo->location)->name ?? 'N/A';
    }

    public function getPrefix()
    {
        if ( ! $this->providerInfo) {
            return '';
        }

        return $this->providerInfo->prefix;
    }

    public function getPrimaryPhone()
    {
        if ( ! $this->phoneNumbers) {
            return '';
        }
        $phoneNumber = $this->phoneNumbers->where('is_primary', 1)->first();
        if ($phoneNumber) {
            return $phoneNumber->number_with_dashes;
        }

        return '';
    }

    public function getPrimaryPracticeId()
    {
        return $this->program_id;
    }

    /**
     * Get primary practice's name.
     *
     * @return string
     */
    public function getPrimaryPracticeName()
    {
        return ucwords(optional($this->primaryPractice)->display_name);
    }

    /**
     * Get the User's Problems to populate the User header.
     *
     * @return array
     */
    public function getProblemsToMonitor()
    {
        return $this->ccdProblems
            ->sortBy('cpmProblem.name')
            ->pluck('cpmProblem.name', 'cpmProblem.id')
            ->all();
    }

    public function getRegistrationDate()
    {
        return $this->user_registered;
    }

    public function getRules()
    {
        return $this->rules;
    }

    /**
     * Get the value used to index the model.
     *
     * @return mixed
     */
    public function getScoutKey()
    {
        return $this->id;
    }

    public function getSendAlertTo()
    {
        $ctmsa = [];
        if ( ! $this->careTeamMembers) {
            return '';
        }
        if ($this->careTeamMembers->count() > 0) {
            foreach ($this->careTeamMembers as $careTeamMember) {
                if ($careTeamMember->alert) {
                    $ctmsa[] = $careTeamMember->member_user_id;
                }
            }
        }

        return $ctmsa;
    }

    public function getSpecialty()
    {
        if ( ! $this->providerInfo) {
            return '';
        }

        return $this->providerInfo->specialty;
    }

    public function getSuffix()
    {
        return $this->suffix ?? '';
    }

    public function getUCP()
    {
        $userUcp = $this->ucp()->with(
            [
                'item.meta',
                'item.question',
            ]
        )->get();
        $userUcpData = [
            'ucp'        => [],
            'obs_keys'   => [],
            'alert_keys' => [],
        ];
        if ($userUcp->count() > 0) {
            foreach ($userUcp as $userUcpItem) {
                $userUcpData['ucp'][] = $userUcpItem;
                if (isset($userUcpItem->item->question)) {
                    $question = $userUcpItem->item->question;
                    if ($question) {
                        // obs key should be unique
                        $userUcpData['obs_keys'][$question->obs_key] = $userUcpItem->meta_value;
                    }
                }

                if (isset($userUcpItem->item->meta)) {
                    $alert_key = $userUcpItem->item->meta()->where('meta_key', '=', 'alert_key')->first();
                    if ($alert_key) {
                        // alert_key should be unique
                        $userUcpData['alert_keys'][$alert_key->meta_value] = $userUcpItem->meta_value;
                    }
                }
            }
            $userUcpData['ucp'] = collect($userUcpData['ucp']);
        }

        return $userUcpData;
    }

    public function getWithdrawnReason()
    {
        if ( ! $this->patientInfo) {
            return '';
        }

        return $this->patientInfo->withdrawn_reason;
    }

    public function getWorkPhoneNumber()
    {
        if ( ! $this->phoneNumbers) {
            return '';
        }
        $phoneNumber = $this->phoneNumbers->where('type', 'work')->first();
        if ($phoneNumber) {
            return $phoneNumber->number;
        }

        return '';
    }

    public function hasCcda()
    {
        if ( ! $this->id) {
            return null;
        }

        $cacheKey = str_replace('{$userId}', $this->id, Constants::CACHE_USER_HAS_CCDA);

        return Cache::remember(
            $cacheKey,
            1,
            function () {
                return $this->ccdas()->exists();
            }
        );
    }

    public function hasProblem($problem)
    {
        return ! $this->ccdProblems->where('cpm_problem_id', '=', $problem)->isEmpty();
    }

    public function hasScheduledCallToday()
    {
        return Call::where(
            function ($q) {
                $q->whereNull('type')
                    ->orWhere('type', '=', 'call');
            }
        )
            ->where('inbound_cpm_id', $this->id)
            ->where('status', 'scheduled')
            ->where('scheduled_date', '=', Carbon::today()->format('Y-m-d'))
            ->exists();
    }

    public function inboundActivities()
    {
        return $this->hasMany(Call::class, 'inbound_cpm_id', 'id');
    }

    /**
     * Calls made from CLH to the User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function inboundCalls()
    {
        return $this->hasMany(Call::class, 'inbound_cpm_id', 'id')
            ->where(
                function ($q) {
                    $q->whereNull('type')
                        ->orWhere('type', '=', 'call');
                }
            );
    }

    public function inboundMessages()
    {
        return $this->hasMany(Message::class, 'receiver_cpm_id', 'id');
    }

    public function inboundScheduledActivities(Carbon $after = null)
    {
        return $this->inboundActivities()
            ->where('status', '=', Call::SCHEDULED)
            ->when(
                $after,
                function ($query) use ($after) {
                    return $query->where('scheduled_date', '>=', $after->toDateString());
                }
            )
            ->where('called_date', '=', null);
    }

    public function inboundScheduledCalls(Carbon $after = null)
    {
        return $this->inboundCalls()
            ->where('status', '=', Call::SCHEDULED)
            ->when(
                $after,
                function ($query) use ($after) {
                    return $query->where('scheduled_date', '>=', $after->toDateString());
                }
            )
            ->where('called_date', '=', null);
    }

    /**
     * Returns whether the user is an administrator.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('administrator');
    }

    /**
     * Determine whether the User is BHI chargeable (ie. eligible and enrolled).
     *
     * @return bool
     */
    public function isBhi()
    {
        //Do we wanna cache this for a minute maybe?
//        return \Cache::remember("user:$this->id:is_bhi", 1, function (){
        return User::isBhiChargeable()
            ->where('id', $this->id)
            ->exists();
//        });
    }

    /**
     * Returns whether the user is an administrator.
     *
     * @param bool $includeViewOnly
     */
    public function isCareAmbassador($includeViewOnly = true): bool
    {
        $arr = ['care-ambassador'];
        if ($includeViewOnly) {
            $arr[] = 'care-ambassador-view-only';
        }

        return $this->hasRole($arr);
    }

    /**
     * Returns whether the user is a Care Coach (AKA Care Center).
     * A Care Coach can be employed from CLH ['care-center']
     * or not ['care-center-external'].
     */
    public function isCareCoach(): bool
    {
        return $this->hasRole(['care-center', 'care-center-external']);
    }

    public function isCcm()
    {
        return $this->ccmNoOfMonitoredProblems() >= 1;
    }

    public function isCCMCountable(): bool
    {
        return $this->hasRole(Role::CCM_TIME_ROLES);
    }

    /**
     * Determines whether a patient is eligible to enroll.
     *
     * @return bool
     */
    public function isCcmEligible()
    {
        return 'to_enroll' == $this->getCcmStatus();
    }

    /**
     * Returns true if the patient has CCM and the patient's practice has G2058 chargeable service code enabled.
     *
     * @return bool
     */
    public function isCcmPlus()
    {
        return $this->isCcm() && $this->primaryPractice->hasCCMPlusServiceCode();
    }

    /**
     * Returns whether the user is an administrator.
     */
    public function isEhrReportWriter(): bool
    {
        return $this->hasRole('ehr-report-writer');
    }

    public function isInternalUser()
    {
        return $this->hasRole(Constants::CLH_INTERNAL_USER_ROLE_NAMES);
    }

    /**
     * Determine whether the User is Legacy BHI eligible.
     * "Legacy BHI Eligible" applies to a small number of patients who are BHI eligible, but consented before
     * 7/23/2018.
     * On 7/23/2018 we changed our Terms and Conditions to include BHI, so patients who consented before 7/23 need a
     * separate consent for BHI.
     *
     * @return bool
     */
    public function isLegacyBhiEligible()
    {
        //Do we wanna cache this for a minute maybe?
//        return \Cache::remember("user:$this->id:is_bhi_eligible", 1, function (){
        return User::isBhiEligible()
            ->where('id', $this->id)
            ->exists();
//        });
    }

    /**
     * Returns whether the user is a participant.
     */
    public function isParticipant(): bool
    {
        return $this->hasRole('participant');
    }

    public function isPcm()
    {
        if ($this->ccmNoOfMonitoredProblems() >= 2) {
            return false;
        }

        return User::whereHas('ccdProblems', function ($q) {
            $q->where(function ($q) {
                $q->whereHas('codes', function ($q) {
                    $q->whereIn('code', function ($q) {
                        $q->select('code')->from('pcm_problems')->where('practice_id', '=', $this->program_id);
                    });
                })->orWhereHas('cpmProblem', function ($q) {
                    $q->whereIn('default_icd_10_code', function ($q) {
                        $q->select('code')->from('pcm_problems')->where('practice_id', '=', $this->program_id);
                    })->orWhereIn('name', function ($q) {
                        $q->select('description')->from('pcm_problems')->where('practice_id', '=', $this->program_id);
                    });
                });
            })->orWhere(function ($q) {
                $q->whereIn('name', function ($q) {
                    $q->select('description')->from('pcm_problems')->where('practice_id', '=', $this->program_id);
                });
            });
        })
            ->where('id', '=', $this->id)
            ->exists();
    }

    public function isPracticeStaff(): bool
    {
        return $this->hasRole(Constants::PRACTICE_STAFF_ROLE_NAMES);
    }

    /**
     * Returns whether the user is an administrator.
     */
    public function isProvider(): bool
    {
        return $this->hasRole('provider');
    }

    /**
     * Returns whether the user is an administrator.
     */
    public function isSaasAdmin(): bool
    {
        return $this->hasRole('saas-admin');
    }

    /**
     * Returns whether the user is a Software Only user.
     */
    public function isSoftwareOnly(): bool
    {
        return $this->hasRole('software-only');
    }

    /**
     * Returns if this is a Survey-Only user. A Survey-Only user is meant to do Self Enrollment as an Enrollee, or Unreachable Patient.
     *
     * @return bool
     */
    public function isSurveyOnly()
    {
        return $this->hasRole(self::SURVEY_ONLY);
    }

    public function lastObservation()
    {
        return $this->observations()->orderBy('id', 'desc');
    }

    public function latestCcda()
    {
        return $this->ccdas()
            ->orderBy('updated_at', 'desc')
            ->first();
    }

    /**
     * Get billing provider.
     */
    public function leadContact(): User
    {
        $leadContact = $this->careTeamMembers
            ->where('type', 'lead_contact')
            ->first();

        return $leadContact->user ?? new User();
    }

    public function linkToViewResource()
    {
        if ($this->isInternalUser()) {
            return route('admin.users.edit', ['id' => $this->id]);
        }

        if ($this->isParticipant()) {
            return route('patient.careplan.print', ['patientId' => $this->id]);
        }

        if ($this->isPracticeStaff()) {
            return route('provider.dashboard.manage.staff', ['practiceSlug' => $this->practices->first()->name]);
        }
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class)
            ->withTimestamps();
    }

    public function loginEvents()
    {
        return $this->hasMany(LoginLogout::class, 'user_id', 'id');
    }

    public function name()
    {
        return $this->display_name ?? ($this->getFirstName().$this->getLastName());
    }

    public function notes()
    {
        return $this->hasMany('App\Note', 'patient_id', 'id');
    }

    /**
     * Extra time worked, or cash bonuses for Nurses.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function nurseBonuses()
    {
        return $this->hasMany(NurseInvoiceExtra::class);
    }

    public function nurseInfo()
    {
        return $this->hasOne(Nurse::class, 'user_id', 'id');
    }

    public function observations()
    {
        return $this->hasMany('App\Observation', 'user_id', 'id');
    }

    public function onFirstCall(): bool
    {
        return 0 == $this->inboundCalls()
            ->where('status', 'reached')->count();
    }

    /**
     * Calls made from the User to CLH.
     * Does not include task type calls i.e callbacks.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function outboundCalls()
    {
        return $this->hasMany(Call::class, 'outbound_cpm_id', 'id')
            ->where(
                function ($q) {
                    $q->whereNull('type')
                        ->orWhere('type', '=', 'call');
                }
            );
    }

    public function outboundMessages()
    {
        return $this->hasMany(Message::class, 'sender_cpm_id', 'id');
    }

    public function pageTimersAsProvider()
    {
        return $this->hasMany(PageTimer::class, 'provider_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function passwordsHistory()
    {
        return $this->hasOne(UserPasswordsHistory::class, 'user_id');
    }

    public function patientActivities()
    {
        return $this->hasMany(\CircleLinkHealth\TimeTracking\Entities\Activity::class, 'patient_id', 'id');
    }

    public function patientAWVSummaries()
    {
        return $this->hasMany(PatientAWVSummary::class, 'user_id');
    }

    public function patientCcmStatusRevisions()
    {
        return $this->hasMany(PatientCcmStatusRevision::class, 'patient_user_id');
    }

    public function patientInfo()
    {
        return $this->hasOne(Patient::class, 'user_id', 'id');
    }

    public function patientIsUPG0506(): bool
    {
        if ( ! $this->patientInfo || ! $this->isParticipant()) {
            return false;
        }

        $ccda = $this->ccdas->first();
        if ( ! $ccda) {
            return false;
        }

        if ( ! $ccda->hasUPG0506PdfCareplanMedia()->exists()) {
            return false;
        }

        return true;
    }

    public function patientList()
    {
        return User::intersectPracticesWith($this)
            ->ofType('participant')
            ->whereHas('patientInfo')
            ->with(
                [
                    'observations' => function ($query) {
                        $query->where('obs_key', '!=', 'Outbound');
                        $query->orderBy('obs_date', 'DESC');
                        $query->first();
                    },
                    'careTeamMembers' => function ($q) {
                        $q->where('type', '=', CarePerson::BILLING_PROVIDER)
                            ->with('user');
                    },
                    'phoneNumbers' => function ($q) {
                        $q->where('type', '=', PhoneNumber::HOME);
                    },
                    'carePlan.providerApproverUser',
                    'primaryPractice',
                    'patientInfo.location',
                ]
            )
            ->get();
    }

    public function patientNurseAsPatient()
    {
        return $this->hasOne(PatientNurse::class, 'patient_user_id');
    }

    public function patientProblemsForBillingProcessing(): Collection
    {
        return  $this->ccdProblems->map(function (Problem $p) {
            return (new PatientProblemForProcessing())
                ->setId($p->id)
                ->setCode($p->icd10Code())
                ->setServiceCodes($p->chargeableServiceCodesForLocation($this->patientInfo->preferred_contact_location));
        });
    }

    public function patientSummaries()
    {
        return $this->hasMany(PatientMonthlySummary::class, 'patient_id');
    }

    public function patientSummaryForMonth(Carbon $date = null)
    {
        return $this->patientSummaries()
            ->orderBy('id', 'desc')
            ->whereMonthYear(($date ?? Carbon::now())->startOfMonth())
            ->first();
    }

    public function phoneNumbers()
    {
        return $this->hasMany(PhoneNumber::class, 'user_id', 'id');
    }

    /**
     * Get the specified Practice, if it is related to this User
     * You can pass in a practice_id, practice_slug, or  CircleLinkHealth\Customer\Entities\Practice object.
     *
     * @param $practice
     *
     * @return mixed
     */
    public function practice($practice)
    {
        return Cache::remember("{$this->id}_practice", 1, function () use ($practice) {
            if (is_string($practice) && ! is_int($practice)) {
                return $this->practices()
                    ->where('name', '=', $practice)
                    ->first();
            }

            $practiceId = parseIds($practice);

            if ( ! $practiceId) {
                return null;
            }

            return $this->practices()
                ->where('program_id', '=', $practiceId[0])
                ->first();
        });
    }

    /**
     * Get the User's Primary Or Global Role.
     *
     * @return Role|null
     */
    public function practiceOrGlobalRole()
    {
        if ($this->practice($this->primaryPractice)) {
            $primaryPractice = $this->practice($this->primaryPractice);

            if ($primaryPractice->pivot->role_id) {
                return Role::find($primaryPractice->pivot->role_id);
            }
        }

        return $this->roles->first();
    }

    public function practices(
        bool $onlyActive = false,
        bool $onlyEnrolledPatients = false,
        array $ofRoleIds = null
    ) {
        return $this->belongsToMany(Practice::class, 'practice_role_user', 'user_id', 'program_id')
            ->withPivot('role_id')
            ->when(
                $onlyActive,
                function ($query) use ($onlyActive) {
                    return $query->where('active', '=', 1);
                }
            )
            ->when(
                $onlyEnrolledPatients,
                function ($query) use ($onlyEnrolledPatients) {
                    //$query -> Practice Model
                    return $query->whereHas(
                        'patients',
                        function ($innerQuery) {
                            //$innerQuery -> User Model
                            return $innerQuery->whereHas(
                                'patientInfo',
                                function ($innerInnerQuery) {
                                    //$innerInnerQuery -> Patient model
                                    return $innerInnerQuery->where('ccm_status', '=', 'enrolled');
                                }
                            );
                        }
                    );
                }
            )
            ->when(
                $ofRoleIds,
                function ($query) use ($ofRoleIds) {
                    return $query->whereIn('practice_role_user.role_id', $ofRoleIds);
                }
            )
            ->withTimestamps();
    }

    public function practiceSettings()
    {
        if ( ! $this->primaryPractice) {
            return null;
        }

        return $this->primaryPractice->cpmSettings();
    }

    public function primaryPractice()
    {
        return $this->belongsTo(Practice::class, 'program_id', 'id');
    }

    public function primaryProgramId()
    {
        return $this->program_id;
    }

    public function primaryProgramName()
    {
        return $this->primaryPractice->display_name;
    }

    public function primaryProgramPhoneE164()
    {
        return formatPhoneNumberE164(optional($this->primaryPractice)->outgoing_phone_number);
    }

    public function problemsWithIcd10Code()
    {
        $billableProblems = new Collection();

        $ccdProblems = $this->ccdProblems()
            ->with('icd10Codes')
            ->with('cpmProblem')
            ->whereNotNull('cpm_problem_id')
            ->groupBy('cpm_problem_id')
            ->get()
            ->map(
                function ($problem) use ($billableProblems) {
                    $problem->billing_code = $problem->icd10Code();

                    if ( ! $problem->billing_code) {
                        return $problem;
                    }

                    if ($problem->icd10Codes()->exists()) {
                        $billableProblems->prepend($problem);

                        return $problem;
                    }

                    $billableProblems->push($problem);

                    return $problem;
                }
            );

        return $billableProblems;
    }

    public function providerInfo()
    {
        return $this->hasOne('CircleLinkHealth\Customer\Entities\ProviderInfo', 'user_id', 'id');
    }

    public function receivesBroadcastNotificationsOn()
    {
        return 'App.User.'.$this->id;
    }

    /**
     * Get the regular doctor.
     *
     * @return User
     */
    public function regularDoctor()
    {
        return $this->careTeamMembers()->where('type', '=', CarePerson::REGULAR_DOCTOR);
    }

    /**
     * Get regular doctor User.
     */
    public function regularDoctorUser(): ?User
    {
        return $this->regularDoctor->isEmpty()
            ? null
            : $this->regularDoctor->first()->user;
    }

    public function rolesInPractice($practiceId)
    {
        return $this->roles()
            ->wherePivot('program_id', '=', $practiceId)
            ->get();
    }

    public function routeNotificationForMail($notification)
    {
        $this->loadMissing('primaryPractice');

        if ($this->primaryPractice && $this->primaryPractice->is_demo && isSelfEnrollmentTestModeEnabled()) {
            $hasTester = AppConfig::pull('tester_email', null);

            return $hasTester ?? $this->email;
        }

        return $this->email;
    }

    public function routeNotificationForTwilio()
    {
        $this->loadMissing('primaryPractice');

        if ($this->primaryPractice && $this->primaryPractice->is_demo && isSelfEnrollmentTestModeEnabled()) {
            $hasTester = AppConfig::pull('tester_phone', null);

            return $hasTester ?? $this->getPhoneNumberForSms();
        }

        return $this->getPhoneNumberForSms();
    }

    public function saasAccountName()
    {
        $saasAccount = $this->saasAccount;
        if ($saasAccount) {
            return $saasAccount->name;
        }
        $saasAccount = optional($this->primaryPractice)->saasAccount;
        if ( ! $saasAccount) {
            if (auth()->check()) {
                $saasAccount = auth()->user()->saasAccount;
            }
        }
        if ($saasAccount) {
            $this->saasAccount()
                ->associate($saasAccount);

            return $saasAccount->name;
        }

        return 'CircleLink Health';
    }

    public function safe()
    {
        return [
            'id'            => $this->id,
            'username'      => $this->username,
            'name'          => $this->name(),
            'address'       => $this->address,
            'city'          => $this->city,
            'state'         => $this->state,
            'specialty'     => $this->getSpecialty(),
            'program_id'    => $this->program_id,
            'status'        => $this->status,
            'user_status'   => $this->user_status,
            'is_online'     => $this->is_online,
            'provider_info' => $this->providerInfo,
            'phone'         => $this->getPhone(),
            'created_at'    => optional($this->created_at)->format('c') ?? null,
            'updated_at'    => optional($this->updated_at)->format('c') ?? null,
        ];
    }

    public function scopeCareCoaches($query)
    {
        return $query->ofType(['care-center', 'care-center-external']);
    }

    /**
     * Scope a query to include users NOT of a given type (Role).
     *
     * @param $query
     * @param $type
     */
    public function scopeExceptType(
        $query,
        $type
    ) {
        $query->whereHas(
            'roles',
            function ($q) use (
                $type
            ) {
                if (is_array($type)) {
                    $q->whereNotIn('name', $type);
                } else {
                    $q->where('name', '!=', $type);
                }
            }
        );
    }

    public function scopeHasBillingProvider(
        $query,
        $billing_provider_id
    ) {
        return $query->whereHas(
            'careTeamMembers',
            function ($k) use (
                $billing_provider_id
            ) {
                $k->whereType('billing_provider')
                    ->whereMemberUserId($billing_provider_id);
            }
        );
    }

    /*public function hasScheduledCallThisWeek()
    {
        $weekStart = Carbon::now()->startOfWeek()->toDateString();
        $weekEnd = Carbon::now()->endOfWeek()->toDateString();

        return Call::where(function ($q) {
            $q->whereNull('type')
              ->orWhere('type', '=', 'call');
        })
                   ->where('outbound_cpm_id', $this->id)
                   ->where('status', 'scheduled')
                   ->whereBetween('scheduled_date', [$weekStart, $weekEnd])
                   ->exists();
    }*/

    public function scopeHasSelfEnrollmentInvite($query, Carbon $date = null, $has = true)
    {
        $verb = $has ? 'has' : 'DoesntHave';

        return $query->{"where$verb"}('notifications', function ($q) use ($date) {
            $q->selfEnrollmentInvites()->where('data->is_reminder', false)->when( ! is_null($date), function ($q) use ($date) {
                $q->where([
                    ['created_at', '>=', $date->copy()->startOfDay()],
                    ['created_at', '<=', $date->copy()->endOfDay()],
                ]);
            });
        });
    }

    public function scopeHasSelfEnrollmentInviteReminder($query, Carbon $date = null, $has = true)
    {
        $verb = $has ? 'has' : 'DoesntHave';

        return $query->{"where$verb"}('notifications', function ($q) use ($date) {
            $q->selfEnrollmentInvites()->where('data->is_reminder', true)->when( ! is_null($date), function ($q) use ($date) {
                $q->where([
                    ['created_at', '>=', $date->copy()->startOfDay()],
                    ['created_at', '<=', $date->copy()->endOfDay()],
                ]);
            });
        });
    }

    /**
     * Scope for Enrollable Users we need to send a reminder Self Enrollment Notification to.
     *
     * @param $query
     *
     * @return mixed
     */
    public function scopeHaveEnrollableInvitationDontHaveReminder($query, Carbon $dateInviteSent = null)
    {
        $dateInviteSent = is_null($dateInviteSent) ? now()->subDays(2) : $dateInviteSent;

        return $query->hasSelfEnrollmentInvite($dateInviteSent)
            ->whereHas('patientInfo', function ($patient) {
                $patient->where('ccm_status', Patient::UNREACHABLE);
            })->whereDoesntHave('notifications', function ($notification) use ($dateInviteSent) {
                $notification
                    ->where('data->is_reminder', true)
                    ->selfEnrollmentInvites()
                    ->where('created_at', '>=', $dateInviteSent->copy()->startOfDay());
            });
    }

    /**
     * Scope a query to intersect locations with the given user.
     *
     * @param $query
     * @param $user
     */
    public function scopeIntersectLocationsWith(
        $query,
        $user
    ) {
        $viewableLocations = $user->isAdmin()
            ? Location::all()->pluck('id')->all()
            : $user->locations->pluck('id')->all();

        return $query->whereHas(
            'locations',
            function ($q) use (
                $viewableLocations
            ) {
                $q->whereIn('locations.id', $viewableLocations);
            }
        );
    }

    /**
     * Scope a query to intersect practices with the given user.
     *
     * @param $query
     * @param $user
     *
     * @return
     */
    public function scopeIntersectPracticesWith(
        $query,
        User $user,
        bool $withDemo = true
    ) {
        $viewablePractices = $user->viewableProgramIds($withDemo);

        return $query->whereHas(
            'practices',
            function ($q) use (
                $viewablePractices
            ) {
                $q->whereIn('practices.id', $viewablePractices);
            }
        );
    }

    /**
     * Scope for patients who can be charged for BHI.
     *
     * Conditions are:
     *      1. Patient is Enrolled
     *      2. Patient's Primary Practice is chargeable for BHI
     *      3. Patient has at least one BHI problem
     *      4. Patient has consented for BHI
     *
     * @param $builder
     *
     * @return mixed
     */
    public function scopeIsBhiChargeable($builder)
    {
        return $builder
            ->whereHas(
                'primaryPractice',
                function ($q) {
                    $q->hasServiceCode('CPT 99484');
                }
            )->whereHas(
                'patientInfo',
                function ($q) {
                    $q->enrolled();
                }
            )
            ->whereHas(
                'ccdProblems.cpmProblem',
                function ($q) {
                    $q->where('is_behavioral', true);
                }
            )
            ->where(
                function ($q) {
                    $q->where(function ($q) {
                        $q->notOfPracticeRequiringSpecialBhiConsent()
                            ->whereHas(
                                'patientInfo',
                                function ($q) {
                                    $q->where('consent_date', '>=', Patient::DATE_CONSENT_INCLUDES_BHI);
                                }
                            );
                    })->orWhere(function ($q) {
                        $q->orWhereHas(
                            'notes',
                            function ($q) {
                                $q->where('type', '=', Patient::BHI_CONSENT_NOTE_TYPE);
                            }
                        );
                    });
                }
            );
    }

    /**
     * Scope for patients who are eligible for BHI.
     *
     * Conditions are:
     *      1. Patient is Enrolled
     *      2. Patient's Primary Practice is chargeable for BHI
     *      3. Patient has at least one BHI problem
     *      4. Patient has NOT consented to receive BHI services yet.
     *
     * @param $builder
     *
     * @return mixed
     */
    public function scopeIsBhiEligible($builder)
    {
        return $builder
            ->whereHas(
                'primaryPractice',
                function ($q) {
                    $q->hasServiceCode('CPT 99484');
                }
            )
            ->where(function ($q) {
                $q->where(function ($q) {
                    $q->notOfPracticeRequiringSpecialBhiConsent()
                        ->whereHas(
                            'patientInfo',
                            function ($q) {
                                $q->enrolled()
                                    ->where('consent_date', '<', Patient::DATE_CONSENT_INCLUDES_BHI);
                            }
                        );
                })->orWhere(function ($q) {
                    $q->ofPracticeRequiringSpecialBhiConsent();
                });
            })
            ->whereHas(
                'ccdProblems.cpmProblem',
                function ($q) {
                    $q->where('is_behavioral', true);
                }
            )
            ->whereDoesntHave(
                'notes',
                function ($q) {
                    $q->where('type', '=', Patient::BHI_REJECTION_NOTE_TYPE);
                }
            )
            ->whereDoesntHave(
                'notes',
                function ($q) {
                    $q->where('type', '=', Patient::BHI_CONSENT_NOTE_TYPE);
                }
            );
    }

    /**
     *For the moment we will just check if primary practice is not demo.
     * May implement something else in the future.
     *
     * @param mixed $query
     */
    public function scopeIsNotDemo($query)
    {
        $demoPracticeIds = Practice::whereIsDemo(1)->pluck('id')->toArray();

        //specifying table - there cases that program_id is ambiguous (e.g. if practice_role_users table exists in the query)
        return $query->whereNotIn('users.program_id', $demoPracticeIds);
    }

    /**
     * Scope for patients who do not belong Practices that have exclusively requested us to show BHI flag for their
     * patients. Default operator is "whereIn", which will show patients who belong to such practices. Changet to
     * "whereNotIn", to show patients who do not belong to such practices.
     *
     * See ticket https://circlelinkhealth.atlassian.net/browse/CPM-1784
     *
     * @param $builder
     * @param string $operator
     *
     * @return mixed
     */
    public function scopeNotOfPracticeRequiringSpecialBhiConsent($builder)
    {
        return $this->queryOfPracticesRequiringSpecialBhiConsent($builder, 'whereNotIn');
    }

    /**
     * Scope for patients who belong to active and billable practices.
     *
     * @param $query
     * @param bool $includeDemo
     */
    public function scopeOfActiveBillablePractice($query, $includeDemo = true)
    {
        $query->whereHas(
            'practices',
            function ($q) use ($includeDemo) {
                $q->activeBillable()
                    ->when(false === $includeDemo, function ($q) {
                        $q->whereIsDemo(0);
                    });
            }
        );
    }

    /**
     * Scope for patients who belong to one of the given practice IDs.
     *
     * @param $query
     * @param $practiceId
     */
    public function scopeOfPractice($query, $practiceId)
    {
        $practiceId = parseIds($practiceId);

        $query->whereHas(
            'practices',
            function ($q) use ($practiceId) {
                $q->whereIn('practices.id', $practiceId);
            }
        )->orWhereIn('program_id', $practiceId);
    }

    /**
     * Scope for patients who belong to Practices that have exclusively requested us to show BHI flag for their
     * patients. Default operator is "whereIn", which will show patients who belong to such practices. Changet to
     * "whereNotIn", to show patients who do not belong to such practices.
     *
     * See ticket https://circlelinkhealth.atlassian.net/browse/CPM-1784
     *
     * @param $builder
     * @param string $operator
     *
     * @return mixed
     */
    public function scopeOfPracticeRequiringSpecialBhiConsent($builder)
    {
        return $this->queryOfPracticesRequiringSpecialBhiConsent($builder, 'whereIn');
    }

    /**
     * Scope a query to only include users of a given type (Role).
     *
     * @param $query
     * @param $type
     * @param mixed $excludeAwv
     */
    public function scopeOfType(
        $query,
        $type,
        $excludeAwv = true
    ) {
        $query->whereHas(
            'roles',
            function ($q) use (
                $type
            ) {
                if (is_array($type)) {
                    $q->whereIn('name', $type);
                } else {
                    $q->where('name', '=', $type);
                }
            }
        );

        $query->when($excludeAwv, function ($q) {
            // we want to exclude only if user has patient info
            // so we have to make sure that if user does not have patientInfo,
            // we do not exclude them
            $q->where(function ($q2) {
                $q2->whereHas('patientInfo', function ($q3) {
                    $q3->where('is_awv', 0);
                })
                    ->orWhereDoesntHave('patientInfo');
            });
        });
    }

    /**
     * @param mixed $query
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopePatientsPendingCLHApproval($query, User $approver)
    {
        return $query->intersectPracticesWith($approver, false)
            ->ofType('participant')
            ->whereHas('patientInfo', function ($q) {
                $q->enrolled();
            })
            ->whereHas(
                'carePlan',
                function ($q) {
                    $q->whereIn('status', [CarePlan::DRAFT]);
                }
            )
            ->with(
                [
                    'observations' => function ($query) {
                        $query->where('obs_key', '!=', 'Outbound');
                        $query->orderBy('obs_date', 'DESC');
                        $query->limit(1);
                    },
                    'phoneNumbers' => function ($q) {
                        $q->where('type', '=', PhoneNumber::HOME);
                    },
                    'patientInfo.location',
                    'primaryPractice',
                    'carePlan',
                ]
            );
    }

    public function scopePatientsPendingProviderApproval($query, User $approver)
    {
        return $query->ofPractice($approver->practices)
            ->ofType('participant')
            ->whereHas('patientInfo', function ($q) {
                $q->enrolled();
            })
            ->whereHas(
                'carePlan',
                function ($q) {
                    $q->where('status', '=', CarePlan::RN_APPROVED);
                }
            )
            ->when($isProvider = $approver->isProvider(), function ($q) use ($approver) {
                if ((bool) $approver->providerInfo->approve_own_care_plans) {
                    $q->whereHas(
                        'billingProvider',
                        function ($q) use ($approver) {
                            $q->where('member_user_id', '=', $approver->id);
                        }
                    );
                }
            })
            ->when(false === $isProvider, function ($q) use ($approver) {
                $q->whereHas(
                    'billingProvider.user.forwardAlertsTo',
                    function ($q) use ($approver) {
                        $q->where('id', '=', $approver->id);
                    }
                );
            })
            ->with(
                [
                    'observations' => function ($query) {
                        $query->where('obs_key', '!=', 'Outbound')->orderBy('obs_date', 'DESC')->limit(1);
                    },
                    'phoneNumbers' => function ($q) {
                        $q->where('type', '=', PhoneNumber::HOME);
                    },
                    'patientInfo.location',
                    'primaryPractice',
                    'carePlan',
                ]
            );
    }

    public function scopePracticeStaff($query)
    {
        return $query->ofType(Constants::PRACTICE_STAFF_ROLE_NAMES);
    }

    /**
     * Scope the query to practices for which the user has at least one of the given roles.
     *
     * @param $query
     */
    public function scopePracticesWhereHasRoles($query, array $roleIds, bool $onlyActive = false)
    {
        $query->whereHas(
            'practices',
            function ($q) use ($roleIds, $onlyActive) {
                return $q
                    ->when(
                        $onlyActive,
                        function ($query) {
                            return $query->where('active', '=', 1);
                        }
                    )
                    ->whereIn('practice_role_user.role_id', $roleIds);
            }
        );
    }

    public function scopeWithCareTeamOfType(
        $query,
        $type
    ) {
        $query->with(
            [
                'careTeamMembers' => function ($q) use (
                    $type
                ) {
                    $q->where('type', $type)
                        ->with('user');
                },
            ]
        );
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeWithDownloadableInvoices($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->careCoaches()->with([
            'nurseInfo' => function ($nurseInfo) use ($startDate, $endDate) {
                $nurseInfo->with(
                    [
                        'invoices' => function ($invoice) use ($startDate) {
                            $invoice->where('month_year', $startDate);
                        },
                    ]
                );
            },
            'pageTimersAsProvider' => function ($pageTimer) use ($startDate, $endDate) {
                $pageTimer->whereBetween('start_time', [$startDate, $endDate]);
            },
        ])->whereHas('nurseInfo.invoices', function ($invoice) use ($startDate) {
            $invoice->where('month_year', $startDate);
        })
            //            Need nurses that are currently active or used to be for selected month
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereHas(
                    'nurseInfo',
                    function ($info) {
                        $info->where('status', 'active')->when(
                            isProductionEnv(),
                            function ($info) {
                                $info->where('is_demo', false);
                            }
                        );
                    }
                )
                    ->orWhereHas('pageTimersAsProvider', function ($pageTimersAsProvider) use ($startDate, $endDate) {
                        $pageTimersAsProvider->whereBetween('start_time', [$startDate, $endDate]);
                    });
            });
    }

    /**
     * Get Scout index name for the model.
     *
     * @return string
     */
    public function searchableAs()
    {
        return 'provider_users_index';
    }

    /**
     * Send a CarePlan Approval reminder, if there are CarePlans pending approval.
     *
     * @param $numberOfCareplans
     * @param bool $force
     *
     * @return bool
     */
    public function sendCarePlanApprovalReminder($numberOfCareplans, $force = false)
    {
        if ( ! $this->shouldSendCarePlanApprovalReminder() && ! $force) {
            return false;
        }

        if ($numberOfCareplans < 1) {
            return false;
        }

        $this->loadMissing(['primaryPractice.settings']);

        $this->notify(new CarePlanApprovalReminder($numberOfCareplans));

        return true;
    }

    /**
     * Send the password reset notification.
     *
     * @param string $token
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    public function service()
    {
        return app(UserService::class);
    }

    public function setActiveDate($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->active_date = $value;
        $this->patientInfo->save();

        return true;
    }

    public function setAgentEmail($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->agent_email = $value;
        $this->patientInfo->save();

        return true;
    }

    public function setAgentName($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->agent_name = $value;
        $this->patientInfo->save();

        return true;
    }

    public function setAgentPhone($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->agent_telephone = $value;
        $this->patientInfo->save();

        return true;
    }

    public function setAgentRelationship($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->agent_relationship = $value;
        $this->patientInfo->save();

        return true;
    }

    public function setAgentTelephone($value)
    {
        return $this->setAgentPhone($value);
    }

    public function setBillingProviderId($value)
    {
        if (empty($value)) {
            Log::debug("Removing provider for enrollee[$this->id] because value[$value] is empty");
            $this->careTeamMembers()->where('type', CarePerson::BILLING_PROVIDER)->delete();

            return true;
        }
        $careTeamMember = $this->careTeamMembers()->where('type', CarePerson::BILLING_PROVIDER)->first();
        if ($careTeamMember) {
            $careTeamMember->member_user_id = $value;
        } else {
            $careTeamMember                 = new CarePerson();
            $careTeamMember->user_id        = $this->id;
            $careTeamMember->member_user_id = $value;
            $careTeamMember->type           = CarePerson::BILLING_PROVIDER;
        }

        if ($careTeamMember->isDirty()) {
            Log::debug("Saving provider[$value] for user[$this->id]");
            $careTeamMember->save();
            $this->load(['billingProvider', 'careTeamMembers']);
        }

        return true;
    }

    public function setBirthDate($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }

        if ( ! is_a($value, Carbon::class)) {
            $value = Carbon::parse($value);
        }

        $this->patientInfo->birth_date = $value;
        $this->patientInfo->save();

        return true;
    }

    public function setCanSeePhi(bool $shouldSee = true)
    {
        $permName = 'phi.read';

        $phiRead = Permission::whereName($permName)->first();
        if ($phiRead) {
            $this->attachPermission($phiRead, $shouldSee);
        }
    }

    public function setCareplanLastPrinted($value)
    {
        if ( ! $this->carePlan) {
            return '';
        }
        $this->carePlan->last_printed = $value;
        $this->carePlan->save();

        return true;
    }

    public function setCarePlanProviderApprover($value)
    {
        if ( ! $this->carePlan) {
            return '';
        }
        $this->carePlan->provider_approver_id = $value;
        $this->carePlan->save();

        return true;
    }

    public function setCarePlanProviderApproverDate($value)
    {
        if ( ! $this->carePlan) {
            return '';
        }
        $this->carePlan->provider_date = $value;
        $this->carePlan->save();

        return true;
    }

    public function setCarePlanQAApprover($value)
    {
        if ( ! $this->carePlan) {
            return '';
        }
        $this->carePlan->qa_approver_id = $value;
        $this->carePlan->save();

        return true;
    }

    public function setCarePlanQADate($value)
    {
        if ( ! $this->carePlan) {
            return '';
        }
        $this->carePlan->qa_date = $value;
        $this->carePlan->save();

        return true;
    }

    public function setCarePlanStatus($value)
    {
        if ( ! $this->carePlan) {
            return '';
        }
        $this->carePlan->status = $value;
        $this->carePlan->save();

        $this->load('carePlan');

        return true;
    }

    public function setCareTeam(array $memberUserIds)
    {
        if ( ! is_array($memberUserIds)) {
            $this->careTeamMembers()->where('type', 'member')->delete();

            return false; // must be array
        }
        $this->careTeamMembers()->where('type', 'member')->whereNotIn(
            'member_user_id',
            $memberUserIds
        )->delete();
        foreach ($memberUserIds as $memberUserId) {
            $careTeamMember = $this->careTeamMembers()->where('type', 'member')->where(
                'member_user_id',
                $memberUserId
            )->first();
            if ($careTeamMember) {
                $careTeamMember->member_user_id = $memberUserId;
            } else {
                $careTeamMember                 = new CarePerson();
                $careTeamMember->user_id        = $this->id;
                $careTeamMember->member_user_id = $memberUserId;
                $careTeamMember->type           = 'member';
            }
            $careTeamMember->save();
        }

        return true;
    }

    public function setCcmStatus($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }

        $this->patientInfo->ccm_status = $value;
    }

    public function setDailyReminderAreas($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->daily_reminder_areas = $value;
        $this->patientInfo->save();

        return true;
    }

    public function setDailyReminderOptin($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->daily_reminder_optin = $value;
        $this->patientInfo->save();

        return true;
    }

    public function setDailyReminderTime($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->daily_reminder_time = $value;
        $this->patientInfo->save();

        return true;
    }

    public function setDatePaused($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->date_paused = $value;
        $this->patientInfo->save();

        return true;
    }

    public function setDateUnreachable($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->date_unreachable = $value;
        $this->patientInfo->save();

        return true;
    }

    public function setDateWithdrawn($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->date_withdrawn = $value;
        $this->patientInfo->save();

        return true;
    }

    public function setEmailAddress($value)
    {
        return $this->email = $value;
    }

    public function setFirstName($value)
    {
        $this->attributes['first_name'] = ucwords($value);
        $this->display_name             = $this->getFullName();
    }

    public function setGender($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->gender = $value;
        $this->patientInfo->save();

        return true;
    }

    public function setHomePhoneNumber($value)
    {
        return $this->setPhone($value);
    }

    public function setHospitalReminderAreas($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->hospital_reminder_areas = $value;
        $this->patientInfo->save();

        return true;
    }

    public function setHospitalReminderOptin($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->hospital_reminder_optin = $value;
        $this->patientInfo->save();

        return true;
    }

    public function setHospitalReminderTime($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->hospital_reminder_time = $value;
        $this->patientInfo->save();

        return true;
    }

    public function setLastName($value)
    {
        $this->attributes['last_name'] = $value;
        $this->display_name            = $this->getFullName();
    }

    public function setLeadContactID($value)
    {
        if (empty($value)) {
            $this->careTeamMembers()->where('type', 'lead_contact')->delete();

            return true;
        }
        $careTeamMember = $this->careTeamMembers()->where('type', 'lead_contact')->first();
        if ($careTeamMember) {
            $careTeamMember->member_user_id = $value;
        } else {
            $careTeamMember                 = new CarePerson();
            $careTeamMember->user_id        = $this->id;
            $careTeamMember->member_user_id = $value;
            $careTeamMember->type           = 'lead_contact';
        }
        $careTeamMember->save();

        return true;
    }

    public function setMobilePhoneNumber($value)
    {
        if ( ! $this->phoneNumbers) {
            return '';
        }
        $phoneNumber = $this->phoneNumbers->where('type', 'mobile')->first();
        if ($phoneNumber) {
            $phoneNumber->number = $value;
        } else {
            $phoneNumber          = new PhoneNumber();
            $phoneNumber->user_id = $this->id;
            $phoneNumber->number  = $value;
            $phoneNumber->type    = 'mobile';
        }
        $phoneNumber->save();

        return true;
    }

    public function setMRN($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->mrn_number = $value;
        $this->patientInfo->save();

        return true;
    }

    public function setMrnNumber($value)
    {
        return $this->setMRN($value);
    }

    public function setNpiNumber($value)
    {
        if ( ! $this->providerInfo) {
            return '';
        }
        $this->providerInfo->npi_number = $value;
        $this->providerInfo->save();
    }

    public function setPhone($value)
    {
        if ( ! $this->phoneNumbers) {
            return '';
        }
        $phoneNumber = $this->phoneNumbers->where('type', 'home')->first();
        if ($phoneNumber) {
            $phoneNumber->number = $value;
        } else {
            $phoneNumber             = new PhoneNumber();
            $phoneNumber->user_id    = $this->id;
            $phoneNumber->is_primary = 1;
            $phoneNumber->number     = $value;
            $phoneNumber->type       = 'home';
        }
        $phoneNumber->save();

        return true;
    }

    public function setPreferredCcContactDays($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->preferred_cc_contact_days = $value;
        $this->patientInfo->save();

        return true;
    }

    public function setPreferredContactLanguage($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->preferred_contact_language = $value;
        $this->patientInfo->save();

        return true;
    }

    public function setPreferredContactLocation($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->preferred_contact_location = $value;
        $this->patientInfo->save();

        return true;
    }

    public function setPreferredContactMethod($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->preferred_contact_method = $value;
        $this->patientInfo->save();

        return true;
    }

    public function setPreferredContactTime($value)
    {
        if ( ! $this->patientInfo) {
            return '';
        }
        $this->patientInfo->preferred_contact_time = $value;
        $this->patientInfo->save();

        return true;
    }

    public function setPrefix($value)
    {
        if ( ! $this->providerInfo) {
            return '';
        }
        $this->providerInfo->prefix = $value;
        $this->providerInfo->save();
    }

    public function setRegistrationDate($value)
    {
        $this->user_registered = $value;
        $this->save();

        return true;
    }

    public function setSendAlertTo($memberUserIds)
    {
        if ( ! is_array($memberUserIds)) {
            $this->careTeamMembers()->where('alert', '=', true)->delete();

            return false; // must be array
        }
        $this->careTeamMembers()->where('alert', '=', true)->whereNotIn(
            'member_user_id',
            $memberUserIds
        )->delete();
        foreach ($memberUserIds as $memberUserId) {
            $careTeamMember = $this->careTeamMembers()->where('alert', '=', false)
                ->where('member_user_id', $memberUserId)
                ->first();
            if ($careTeamMember) {
                $careTeamMember->alert = true;
                $careTeamMember->save();
            }
        }

        return true;
    }

    public function setSpecialty($value)
    {
        if ( ! $this->providerInfo) {
            return '';
        }
        $this->providerInfo->specialty = $value;
        $this->providerInfo->save();
    }

    public function setWorkPhoneNumber($value)
    {
        if ( ! $this->phoneNumbers) {
            return '';
        }
        $phoneNumber = $this->phoneNumbers->where('type', 'work')->first();
        if ($phoneNumber) {
            $phoneNumber->number = $value;
        } else {
            $phoneNumber          = new PhoneNumber();
            $phoneNumber->user_id = $this->id;
            $phoneNumber->number  = $value;
            $phoneNumber->type    = 'work';
        }
        $phoneNumber->save();

        return true;
    }

    public function shouldBeSearchable()
    {
        return $this->loadMissing('roles')->isProvider();
    }

    /**
     * @return bool
     */
    public function shouldSendCarePlanApprovalReminder()
    {
        $settings = $this->emailSettings()->firstOrNew([]);

        if (EmailSettings::DAILY == $settings->frequency) {
            return true;
        }
        if (EmailSettings::WEEKLY == $settings->frequency && 1 == Carbon::today()->dayOfWeek) {
            return true;
        }
        if (EmailSettings::MWF == $settings->frequency && (1 == Carbon::today()->dayOfWeek
                || 3 == Carbon::today()->dayOfWeek
                || 5 == Carbon::today()->dayOfWeek)) {
            return true;
        }

        return false;
    }

    /**
     * Determines whether to show the BHI banner to the logged in user, for a given patient.
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function shouldShowBhiBannerIfPatientHasScheduledCallToday(User $patient)
    {
        return $patient->hasScheduledCallToday()
            && $this->shouldShowBhiFlagFor($patient)
            && ($this->isAdmin() || $this->isCareCoach());
    }

    /**
     * Determines whether to show the BHI banner to the logged in user, for a given patient.
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function shouldShowBhiFlagFor(User $patient)
    {
        return $this->hasPermissionForSite('legacy-bhi-consent-decision.create', $patient->program_id)
            && is_a($patient, self::class)
            && $patient->isLegacyBhiEligible()
            && $patient->billingProviderUser()
            && ! Cache::has(
                $this->getLegacyBhiNursePatientCacheKey($patient->id)
            )
            && ($this->isAdmin() || $this->isCareCoach());
    }

    public function shouldShowCcmPlusBadge()
    {
        return isPatientCcmPlusBadgeEnabled() && $this->isCcmPlus();
    }

    /**
     * Determines if current time is within invoice review period.
     */
    public function shouldShowInvoiceReviewButton(): bool
    {
        $now          = Carbon::now();
        $invoiceMonth = $now->copy()->startOfMonth()->subMonth();

        $invoice = NurseInvoice::where('month_year', $invoiceMonth)
            ->undisputed()
            ->notApproved()
            ->ofNurses(auth()->id())
            ->exists();

        return $invoice && $now->lte(NurseInvoiceDisputeDeadline::for($invoiceMonth));
    }

    public function shouldShowPcmBadge()
    {
        // cache for 24 hours
        return Cache::remember("{$this->id}_pcm_badge", 60 * 24, function () {
            return isPatientPcmBadgeEnabled() && $this->isPcm();
        });
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        //@todo: confirm if scout already does this. Adding for extra clarity
        if ($this->shouldBeSearchable()) {
            return [
                'first_name'   => $this->first_name,
                'last_name'    => $this->last_name,
                'suffix'       => $this->suffix,
                'practice_ids' => $this->practices->pluck('id')->all(),
                'location_ids' => $this->locations->pluck('id')->all(),
            ];
        }

        return [];
    }

    public function ucp()
    {
        return $this->hasMany('App\CPRulesUCP', 'user_id', 'id');
    }

    public function userConfig()
    {
        $key        = 'wp_'.$this->primaryProgramId().'_user_config';
        $userConfig = $this->meta->where('meta_key', $key)->first();
        if ( ! $userConfig) {
            return false;
        }

        return unserialize($userConfig['meta_value']);
    }

    public function viewablePatientIds(): array
    {
        return User::ofType('participant')
            ->whereHas(
                'practices',
                function ($q) {
                    $q->whereIn('program_id', $this->viewableProgramIds());
                }
            )
            ->pluck('id')
            ->all();
    }

    // CPM Models

    public function viewableProgramIds(bool $withDemo = true): array
    {
        return $this->practices
            ->when(false === $withDemo, function ($q) {
                return $q->where('is_demo', false);
            })
            ->pluck('id')
            ->all();
    }

    public function viewableProviderIds()
    {
        // get all patients who are in the same programs
        $programIds = $this->viewableProgramIds();
        $patientIds = User::whereHas(
            'practices',
            function ($q) use (
                $programIds
            ) {
                $q->whereIn('program_id', $programIds);
            }
        );

        $patientIds->whereHas(
            'roles',
            function ($q) {
                $q->where('name', '=', 'provider');
            }
        );

        return $patientIds->pluck('id')->all();
    }

    public function viewableUserIds()
    {
        // get all patients who are in the same programs
        $programIds = $this->viewableProgramIds();
        $patientIds = User::whereHas(
            'practices',
            function ($q) use (
                $programIds
            ) {
                $q->whereIn('practice_role_user.program_id', $programIds);
            }
        );

        return $patientIds->pluck('id')->all();
    }

    private function ccmNoOfMonitoredProblems()
    {
        return $this->ccdProblems()
            ->where('is_monitored', 1)
            ->whereHas(
                'cpmProblem',
                function ($cpm) {
                    return $cpm->where('is_behavioral', 0);
                }
            )
            ->count();
    }

    private function formatNumberForSms($number)
    {
        try {
            return \Propaganistas\LaravelPhone\PhoneNumber::make($number)->formatE164();
        } catch (NumberParseException $e) {
            try {
                return \Propaganistas\LaravelPhone\PhoneNumber::make($number, 'us')->formatE164();
            } catch (\libphonenumber\NumberParseException|NumberParseException $e) {
                Log::warning("Could not parse phone number of user[$this->id]");

                return $number;
            }
        }
    }

    private function isCypriotNumber(\Propaganistas\LaravelPhone\PhoneNumber $phoneNumber)
    {
        try {
            return $phoneNumber->isOfCountry('CY');
        } catch (NumberParseException $e) {
            return false;
        }
    }

    private function queryOfPracticesRequiringSpecialBhiConsent($builder, $operator)
    {
        $practiceNames = PracticesRequiringSpecialBhiConsent::names();

        return $builder->when( ! empty($practiceNames), function ($builder) use ($practiceNames, $operator) {
            return $builder->whereHas(
                'primaryPractice',
                function ($q) use ($practiceNames, $operator) {
                    $q->{$operator}('name', $practiceNames);
                }
            );
        });
    }
}
