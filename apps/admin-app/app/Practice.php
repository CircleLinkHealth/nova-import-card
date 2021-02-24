<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Customer\Entities\Practice as CpmPractice;

/**
 * App\Practice.
 *
 * @property int                                                                                                                      $id
 * @property \Illuminate\Support\Collection|null                                                                                      $importing_hooks
 * @property int|null                                                                                                                 $saas_account_id
 * @property int|null                                                                                                                 $ehr_id
 * @property int|null                                                                                                                 $user_id
 * @property string                                                                                                                   $name
 * @property string|null                                                                                                              $display_name
 * @property int                                                                                                                      $active
 * @property int                                                                                                                      $is_demo
 * @property float|null                                                                                                               $clh_pppm
 * @property int                                                                                                                      $term_days
 * @property string|null                                                                                                              $default_user_scope
 * @property string|null                                                                                                              $federal_tax_id
 * @property int|null                                                                                                                 $same_ehr_login
 * @property int|null                                                                                                                 $same_clinical_contact
 * @property int                                                                                                                      $auto_approve_careplans
 * @property int                                                                                                                      $send_alerts
 * @property string|null                                                                                                              $weekly_report_recipients
 * @property string|null                                                                                                              $invoice_recipients
 * @property string|null                                                                                                              $bill_to_name
 * @property string|null                                                                                                              $external_id
 * @property string                                                                                                                   $outgoing_phone_number
 * @property \Illuminate\Support\Carbon|null                                                                                          $created_at
 * @property \Illuminate\Support\Carbon|null                                                                                          $updated_at
 * @property \Illuminate\Support\Carbon|null                                                                                          $deleted_at
 * @property string|null                                                                                                              $sms_marketing_number
 * @property \Illuminate\Database\Eloquent\Collection|\Laravel\Nova\Actions\ActionEvent[]                                             $actions
 * @property int|null                                                                                                                 $actions_count
 * @property \CircleLinkHealth\Customer\Entities\ChargeableService[]|\Illuminate\Database\Eloquent\Collection                         $allChargeableServices
 * @property int|null                                                                                                                 $all_chargeable_services_count
 * @property \CircleLinkHealth\SharedModels\Entities\CareAmbassadorLog[]|\Illuminate\Database\Eloquent\Collection                     $careAmbassadorLogs
 * @property int|null                                                                                                                 $care_ambassador_logs_count
 * @property \CircleLinkHealth\SharedModels\Entities\CarePlanTemplate[]|\Illuminate\Database\Eloquent\Collection                      $careplan
 * @property int|null                                                                                                                 $careplan_count
 * @property \CircleLinkHealth\Customer\Entities\ChargeableService[]|\Illuminate\Database\Eloquent\Collection                         $chargeableServices
 * @property int|null                                                                                                                 $chargeable_services_count
 * @property \CircleLinkHealth\Customer\Entities\Ehr|null                                                                             $ehr
 * @property \CircleLinkHealth\Customer\Filters\EnrolleeCustomFilter[]|\Illuminate\Database\Eloquent\Collection                       $enrolleeCustomFilters
 * @property int|null                                                                                                                 $enrollee_custom_filters_count
 * @property \CircleLinkHealth\SharedModels\Entities\PracticeEnrollmentTips|null                                                      $enrollmentTips
 * @property mixed                                                                                                                    $formatted_name
 * @property string                                                                                                                   $number_with_dashes
 * @property mixed                                                                                                                    $primary_location_id
 * @property mixed                                                                                                                    $subdomain
 * @property \CircleLinkHealth\Customer\Entities\User|null                                                                            $lead
 * @property \CircleLinkHealth\Customer\Entities\Location[]|\Illuminate\Database\Eloquent\Collection                                  $locations
 * @property int|null                                                                                                                 $locations_count
 * @property \CircleLinkHealth\Customer\Entities\Media[]|\Illuminate\Database\Eloquent\Collection                                     $media
 * @property int|null                                                                                                                 $media_count
 * @property \CircleLinkHealth\Customer\Entities\CustomerNotificationContactTimePreference[]|\Illuminate\Database\Eloquent\Collection $notificationContactPreferences
 * @property int|null                                                                                                                 $notification_contact_preferences_count
 * @property \Illuminate\Notifications\DatabaseNotification[]|\Illuminate\Notifications\DatabaseNotificationCollection                $notifications
 * @property int|null                                                                                                                 $notifications_count
 * @property \CircleLinkHealth\SharedModels\Entities\PcmProblem[]|\Illuminate\Database\Eloquent\Collection                            $pcmProblems
 * @property int|null                                                                                                                 $pcm_problems_count
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection                              $revisionHistory
 * @property int|null                                                                                                                 $revision_history_count
 * @property \CircleLinkHealth\SharedModels\Entities\RpmProblem[]|\Illuminate\Database\Eloquent\Collection                            $rpmProblems
 * @property int|null                                                                                                                 $rpm_problems_count
 * @property \CircleLinkHealth\Customer\Entities\SaasAccount|null                                                                     $saasAccount
 * @property \CircleLinkHealth\Customer\Entities\Settings[]|\Illuminate\Database\Eloquent\Collection                                  $settings
 * @property int|null                                                                                                                 $settings_count
 * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection                                      $users
 * @property int|null                                                                                                                 $users_count
 * @method static                                                                                                                   \Illuminate\Database\Eloquent\Builder|Practice active()
 * @method static                                                                                                                   \Illuminate\Database\Eloquent\Builder|Practice activeBillable()
 * @method static                                                                                                                   \Illuminate\Database\Eloquent\Builder|Practice authUserCanAccess($softwareOnly = false)
 * @method static                                                                                                                   \Illuminate\Database\Eloquent\Builder|Practice authUserCannotAccess()
 * @method static                                                                                                                   \Illuminate\Database\Eloquent\Builder|Practice enrolledPatients()
 * @method static                                                                                                                   \Illuminate\Database\Eloquent\Builder|Practice hasImportingHookEnabled(string $hook, string $listener)
 * @method static                                                                                                                   \Illuminate\Database\Eloquent\Builder|Practice hasServiceCode($code)
 * @method static                                                                                                                   \Illuminate\Database\Eloquent\Builder|Practice newModelQuery()
 * @method static                                                                                                                   \Illuminate\Database\Eloquent\Builder|Practice newQuery()
 * @method static                                                                                                                   \Illuminate\Database\Eloquent\Builder|Practice opsDashboardQuery(\Carbon\Carbon $startOfMonth)
 * @method static                                                                                                                   \Illuminate\Database\Eloquent\Builder|Practice query()
 * @method static                                                                                                                   \Illuminate\Database\Eloquent\Builder|Practice whereActive($value)
 * @method static                                                                                                                   \Illuminate\Database\Eloquent\Builder|Practice whereAutoApproveCareplans($value)
 * @method static                                                                                                                   \Illuminate\Database\Eloquent\Builder|Practice whereBillToName($value)
 * @method static                                                                                                                   \Illuminate\Database\Eloquent\Builder|Practice whereClhPppm($value)
 * @method static                                                                                                                   \Illuminate\Database\Eloquent\Builder|Practice whereCreatedAt($value)
 * @method static                                                                                                                   \Illuminate\Database\Eloquent\Builder|Practice whereDefaultUserScope($value)
 * @method static                                                                                                                   \Illuminate\Database\Eloquent\Builder|Practice whereDeletedAt($value)
 * @method static                                                                                                                   \Illuminate\Database\Eloquent\Builder|Practice whereDisplayName($value)
 * @method static                                                                                                                   \Illuminate\Database\Eloquent\Builder|Practice whereEhrId($value)
 * @method static                                                                                                                   \Illuminate\Database\Eloquent\Builder|Practice whereExternalId($value)
 * @method static                                                                                                                   \Illuminate\Database\Eloquent\Builder|Practice whereFederalTaxId($value)
 * @method static                                                                                                                   \Illuminate\Database\Eloquent\Builder|Practice whereId($value)
 * @method static                                                                                                                   \Illuminate\Database\Eloquent\Builder|Practice whereImportingHooks($value)
 * @method static                                                                                                                   \Illuminate\Database\Eloquent\Builder|Practice whereInvoiceRecipients($value)
 * @method static                                                                                                                   \Illuminate\Database\Eloquent\Builder|Practice whereIsDemo($value)
 * @method static                                                                                                                   \Illuminate\Database\Eloquent\Builder|Practice whereName($value)
 * @method static                                                                                                                   \Illuminate\Database\Eloquent\Builder|Practice whereOutgoingPhoneNumber($value)
 * @method static                                                                                                                   \Illuminate\Database\Eloquent\Builder|Practice whereSaasAccountId($value)
 * @method static                                                                                                                   \Illuminate\Database\Eloquent\Builder|Practice whereSameClinicalContact($value)
 * @method static                                                                                                                   \Illuminate\Database\Eloquent\Builder|Practice whereSameEhrLogin($value)
 * @method static                                                                                                                   \Illuminate\Database\Eloquent\Builder|Practice whereSendAlerts($value)
 * @method static                                                                                                                   \Illuminate\Database\Eloquent\Builder|Practice whereSmsMarketingNumber($value)
 * @method static                                                                                                                   \Illuminate\Database\Eloquent\Builder|Practice whereTermDays($value)
 * @method static                                                                                                                   \Illuminate\Database\Eloquent\Builder|Practice whereUpdatedAt($value)
 * @method static                                                                                                                   \Illuminate\Database\Eloquent\Builder|Practice whereUserId($value)
 * @method static                                                                                                                   \Illuminate\Database\Eloquent\Builder|Practice whereWeeklyReportRecipients($value)
 * @mixin \Eloquent
 * @property \CircleLinkHealth\SelfEnrollment\Entities\EnrollmentInvitationLetter|null $enrollmentLetter
 */
class Practice extends CpmPractice
{
    use \Laravel\Nova\Actions\Actionable;
}
