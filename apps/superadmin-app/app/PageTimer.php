<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

/**
 * App\PageTimer.
 *
 * @property int                                                                                         $id
 * @property int                                                                                         $billable_duration
 * @property int                                                                                         $duration
 * @property string|null                                                                                 $duration_unit
 * @property int|null                                                                                    $patient_id
 * @property int|null                                                                                    $enrollee_id
 * @property int|null                                                                                    $provider_id
 * @property int|null                                                                                    $chargeable_service_id
 * @property \Illuminate\Support\Carbon|null                                                             $start_time
 * @property \Illuminate\Support\Carbon|null                                                             $end_time
 * @property string|null                                                                                 $redirect_to
 * @property string|null                                                                                 $url_full
 * @property string|null                                                                                 $url_short
 * @property string                                                                                      $activity_type
 * @property string                                                                                      $title
 * @property string|null                                                                                 $query_string
 * @property int|null                                                                                    $program_id
 * @property string|null                                                                                 $ip_addr
 * @property \Illuminate\Support\Carbon|null                                                             $created_at
 * @property \Illuminate\Support\Carbon|null                                                             $updated_at
 * @property string|null                                                                                 $processed
 * @property string|null                                                                                 $rule_params
 * @property int|null                                                                                    $rule_id
 * @property \Illuminate\Support\Carbon|null                                                             $deleted_at
 * @property string|null                                                                                 $user_agent
 * @property \Illuminate\Database\Eloquent\Collection|\Laravel\Nova\Actions\ActionEvent[]                $actions
 * @property int|null                                                                                    $actions_count
 * @property \CircleLinkHealth\SharedModels\Entities\Activity[]|\Illuminate\Database\Eloquent\Collection $activities
 * @property int|null                                                                                    $activities_count
 * @property \CircleLinkHealth\SharedModels\Entities\Activity                                            $activity
 * @property \CircleLinkHealth\Customer\Entities\ChargeableService|null                                  $chargeableService
 * @property \CircleLinkHealth\Customer\Entities\User|null                                               $logger
 * @property \CircleLinkHealth\Customer\Entities\User|null                                               $patient
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @property int|null                                                                                    $revision_history_count
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer createdInMonth(\Carbon\Carbon $date, string $field = 'created_at')
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer createdOn(\Carbon\Carbon $date, string $field = 'created_at')
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer createdOnIfNotNull(?\Carbon\Carbon $date = null, $field = 'created_at')
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer createdThisMonth(string $field = 'created_at')
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer createdToday(string $field = 'created_at')
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer createdYesterday(string $field = 'created_at')
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer newModelQuery()
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer newQuery()
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer query()
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereActivityType($value)
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereBillableDuration($value)
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereChargeableServiceId($value)
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereCreatedAt($value)
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereDeletedAt($value)
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereDuration($value)
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereDurationUnit($value)
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereEndTime($value)
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereEnrolleeId($value)
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereId($value)
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereIpAddr($value)
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer wherePatientId($value)
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereProcessed($value)
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereProgramId($value)
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereProviderId($value)
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereQueryString($value)
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereRedirectTo($value)
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereRuleId($value)
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereRuleParams($value)
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereStartTime($value)
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereTitle($value)
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereUpdatedAt($value)
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereUrlFull($value)
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereUrlShort($value)
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|PageTimer whereUserAgent($value)
 * @mixin \Eloquent
 */
class PageTimer extends \CircleLinkHealth\SharedModels\Entities\PageTimer
{
    use \Laravel\Nova\Actions\Actionable;
}
