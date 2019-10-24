<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

/**
 * App\User.
 *
 * @property int                                                                                                             $id
 * @property int|null                                                                                                        $saas_account_id
 * @property int                                                                                                             $skip_browser_checks                          Skip compatible browser checks when the user logs in
 * @property int                                                                                                             $count_ccm_time
 * @property string                                                                                                          $username
 * @property string                                                                                                          $program_id
 * @property string                                                                                                          $password
 * @property string                                                                                                          $email
 * @property \Illuminate\Support\Carbon|null                                                                                 $user_registered
 * @property int                                                                                                             $user_status
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
 * @property string|null                                                                                                     $deleted_at
 * @property string|null                                                                                                     $last_login
 * @property int                                                                                                             $is_online
 * @property string|null                                                                                                     $last_session_id
 * @property \CircleLinkHealth\TimeTracking\Entities\Activity[]|\Illuminate\Database\Eloquent\Collection                     $activities
 * @property int|null                                                                                                        $activities_count
 * @property \CircleLinkHealth\TimeTracking\Entities\Activity[]|\Illuminate\Database\Eloquent\Collection                     $activitiesAsProvider
 * @property int|null                                                                                                        $activities_as_provider_count
 * @property \CircleLinkHealth\Customer\Entities\Appointment[]|\Illuminate\Database\Eloquent\Collection                      $appointments
 * @property int|null                                                                                                        $appointments_count
 * @property \CircleLinkHealth\TwoFA\Entities\AuthyUser                                                                      $authyUser
 * @property \App\CareAmbassador                                                                                             $careAmbassador
 * @property \App\CareItem[]|\Illuminate\Database\Eloquent\Collection                                                        $careItems
 * @property int|null                                                                                                        $care_items_count
 * @property \App\CarePlan                                                                                                   $carePlan
 * @property \App\CareplanAssessment                                                                                         $carePlanAssessment
 * @property \CircleLinkHealth\Customer\Entities\CarePerson[]|\Illuminate\Database\Eloquent\Collection                       $careTeamMembers
 * @property int|null                                                                                                        $care_team_members_count
 * @property \App\Models\CCD\Allergy[]|\Illuminate\Database\Eloquent\Collection                                              $ccdAllergies
 * @property int|null                                                                                                        $ccd_allergies_count
 * @property \App\Models\CCD\CcdInsurancePolicy[]|\Illuminate\Database\Eloquent\Collection                                   $ccdInsurancePolicies
 * @property int|null                                                                                                        $ccd_insurance_policies_count
 * @property \App\Models\CCD\Medication[]|\Illuminate\Database\Eloquent\Collection                                           $ccdMedications
 * @property int|null                                                                                                        $ccd_medications_count
 * @property \App\Models\CCD\Problem[]|\Illuminate\Database\Eloquent\Collection                                              $ccdProblems
 * @property int|null                                                                                                        $ccd_problems_count
 * @property \App\Models\MedicalRecords\Ccda[]|\Illuminate\Database\Eloquent\Collection                                      $ccdas
 * @property int|null                                                                                                        $ccdas_count
 * @property \CircleLinkHealth\Customer\Entities\ChargeableService[]|\Illuminate\Database\Eloquent\Collection                $chargeableServices
 * @property int|null                                                                                                        $chargeable_services_count
 * @property \Illuminate\Database\Eloquent\Collection|\Laravel\Passport\Client[]                                             $clients
 * @property int|null                                                                                                        $clients_count
 * @property \CircleLinkHealth\Customer\Entities\Location[]|\Illuminate\Database\Eloquent\Collection                         $clinicalEmergencyContactLocations
 * @property int|null                                                                                                        $clinical_emergency_contact_locations_count
 * @property \App\Comment[]|\Illuminate\Database\Eloquent\Collection                                                         $comment
 * @property int|null                                                                                                        $comment_count
 * @property \App\Models\CPM\CpmBiometric[]|\Illuminate\Database\Eloquent\Collection                                         $cpmBiometrics
 * @property int|null                                                                                                        $cpm_biometrics_count
 * @property \App\Models\CPM\Biometrics\CpmBloodPressure                                                                     $cpmBloodPressure
 * @property \App\Models\CPM\Biometrics\CpmBloodSugar                                                                        $cpmBloodSugar
 * @property \App\Models\CPM\CpmLifestyle[]|\Illuminate\Database\Eloquent\Collection                                         $cpmLifestyles
 * @property int|null                                                                                                        $cpm_lifestyles_count
 * @property \App\Models\CPM\CpmMedicationGroup[]|\Illuminate\Database\Eloquent\Collection                                   $cpmMedicationGroups
 * @property int|null                                                                                                        $cpm_medication_groups_count
 * @property \App\Models\CPM\CpmMiscUser[]|\Illuminate\Database\Eloquent\Collection                                          $cpmMiscUserPivot
 * @property int|null                                                                                                        $cpm_misc_user_pivot_count
 * @property \App\Models\CPM\CpmMisc[]|\Illuminate\Database\Eloquent\Collection                                              $cpmMiscs
 * @property int|null                                                                                                        $cpm_miscs_count
 * @property \App\Models\CPM\CpmProblem[]|\Illuminate\Database\Eloquent\Collection                                           $cpmProblems
 * @property int|null                                                                                                        $cpm_problems_count
 * @property \App\Models\CPM\Biometrics\CpmSmoking                                                                           $cpmSmoking
 * @property \App\Models\CPM\CpmSymptom[]|\Illuminate\Database\Eloquent\Collection                                           $cpmSymptoms
 * @property int|null                                                                                                        $cpm_symptoms_count
 * @property \App\Models\CPM\Biometrics\CpmWeight                                                                            $cpmWeight
 * @property \CircleLinkHealth\NurseInvoices\Entities\Dispute[]|\Illuminate\Database\Eloquent\Collection                     $disputes
 * @property int|null                                                                                                        $disputes_count
 * @property \App\TargetPatient                                                                                              $ehrInfo
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
 * @property \App\Importer\Models\ImportedItems\DemographicsImport[]|\Illuminate\Database\Eloquent\Collection                $patientDemographics
 * @property int|null                                                                                                        $patient_demographics_count
 * @property \CircleLinkHealth\Customer\Entities\Patient                                                                     $patientInfo
 * @property \CircleLinkHealth\Customer\Entities\PatientMonthlySummary[]|\Illuminate\Database\Eloquent\Collection            $patientSummaries
 * @property int|null                                                                                                        $patient_summaries_count
 * @property \CircleLinkHealth\Customer\Entities\Permission[]|\Illuminate\Database\Eloquent\Collection                       $perms
 * @property int|null                                                                                                        $perms_count
 * @property \CircleLinkHealth\Customer\Entities\PhoneNumber[]|\Illuminate\Database\Eloquent\Collection                      $phoneNumbers
 * @property int|null                                                                                                        $phone_numbers_count
 * @property \CircleLinkHealth\Customer\Entities\Practice                                                                    $primaryPractice
 * @property \CircleLinkHealth\Customer\Entities\ProviderInfo                                                                $providerInfo
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[]                                  $revisionHistory
 * @property int|null                                                                                                        $revision_history_count
 * @property \CircleLinkHealth\Customer\Entities\Role[]|\Illuminate\Database\Eloquent\Collection                             $roles
 * @property int|null                                                                                                        $roles_count
 * @property \CircleLinkHealth\Customer\Entities\SaasAccount|null                                                            $saasAccount
 * @property \Illuminate\Database\Eloquent\Collection|\Laravel\Passport\Token[]                                              $tokens
 * @property int|null                                                                                                        $tokens_count
 * @property \App\CPRulesUCP[]|\Illuminate\Database\Eloquent\Collection                                                      $ucp
 * @property int|null                                                                                                        $ucp_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User careCoaches()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User exceptType($type)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User filter(\App\Filters\QueryFilters $filters)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User hasBillingProvider($billing_provider_id)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User intersectLocationsWith($user)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User intersectPracticesWith($user)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User isBhiChargeable()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User isBhiEligible()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User ofActiveBillablePractice()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User ofPractice($practiceId)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\User ofType($type)
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
