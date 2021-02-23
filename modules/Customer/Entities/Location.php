<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary;
use CircleLinkHealth\CcmBilling\Entities\LocationProblemService;
use CircleLinkHealth\CcmBilling\ValueObjects\AvailableServiceProcessors;
use CircleLinkHealth\Core\Traits\Notifiable;
use CircleLinkHealth\Customer\Traits\HasEmrDirectAddress;
use CircleLinkHealth\Synonyms\Traits\Synonymable;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * CircleLinkHealth\Customer\Entities\Location.
 * 
 * CircleLinkHealth\Customer\Entities\Location.
 *
 * @property int                                                                                             $id
 * @property int                                                                                             $practice_id
 * @property int                                                                                             $is_primary
 * @property string|null                                                                                     $external_department_id
 * @property string                                                                                          $name
 * @property string                                                                                          $phone
 * @property string|null                                                                                     $clinical_escalation_phone
 * @property string|null                                                                                     $fax
 * @property string                                                                                          $address_line_1
 * @property string|null                                                                                     $address_line_2
 * @property string                                                                                          $city
 * @property string                                                                                          $state
 * @property string|null                                                                                     $timezone
 * @property string                                                                                          $postal_code
 * @property string|null                                                                                     $ehr_login
 * @property string|null                                                                                     $ehr_password
 * @property \Carbon\Carbon                                                                                  $created_at
 * @property \Carbon\Carbon                                                                                  $updated_at
 * @property string|null                                                                                     $deleted_at
 * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection             $clinicalEmergencyContact
 * @property \CircleLinkHealth\Customer\Entities\EmrDirectAddress[]|\Illuminate\Database\Eloquent\Collection $emrDirect
 * @property mixed                                                                                           $emr_direct_address
 * @property \App\Location                                                                                   $parent
 * @property \CircleLinkHealth\Customer\Entities\Practice                                                    $practice
 * @property \CircleLinkHealth\Customer\Entities\Practice                                                    $program
 * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection             $providers
 * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection             $user
 * @method static                                                                                          bool|null forceDelete()
 * @method static                                                                                          \Illuminate\Database\Query\Builder|\App\Location onlyTrashed()
 * @method static                                                                                          bool|null restore()
 * @method static                                                                                          \Illuminate\Database\Eloquent\Builder|\App\Location whereAddressLine1($value)
 * @method static                                                                                          \Illuminate\Database\Eloquent\Builder|\App\Location whereAddressLine2($value)
 * @method static                                                                                          \Illuminate\Database\Eloquent\Builder|\App\Location whereCity($value)
 * @method static                                                                                          \Illuminate\Database\Eloquent\Builder|\App\Location whereCreatedAt($value)
 * @method static                                                                                          \Illuminate\Database\Eloquent\Builder|\App\Location whereDeletedAt($value)
 * @method static                                                                                          \Illuminate\Database\Eloquent\Builder|\App\Location whereEhrLogin($value)
 * @method static                                                                                          \Illuminate\Database\Eloquent\Builder|\App\Location whereEhrPassword($value)
 * @method static                                                                                          \Illuminate\Database\Eloquent\Builder|\App\Location whereExternalDepartmentId($value)
 * @method static                                                                                          \Illuminate\Database\Eloquent\Builder|\App\Location whereFax($value)
 * @method static                                                                                          \Illuminate\Database\Eloquent\Builder|\App\Location whereId($value)
 * @method static                                                                                          \Illuminate\Database\Eloquent\Builder|\App\Location whereIsPrimary($value)
 * @method static                                                                                          \Illuminate\Database\Eloquent\Builder|\App\Location whereName($value)
 * @method static                                                                                          \Illuminate\Database\Eloquent\Builder|\App\Location wherePhone($value)
 * @method static                                                                                          \Illuminate\Database\Eloquent\Builder|\App\Location wherePostalCode($value)
 * @method static                                                                                          \Illuminate\Database\Eloquent\Builder|\App\Location wherePracticeId($value)
 * @method static                                                                                          \Illuminate\Database\Eloquent\Builder|\App\Location whereState($value)
 * @method static                                                                                          \Illuminate\Database\Eloquent\Builder|\App\Location whereTimezone($value)
 * @method static                                                                                          \Illuminate\Database\Eloquent\Builder|\App\Location whereUpdatedAt($value)
 * @method static                                                                                          \Illuminate\Database\Query\Builder|\App\Location withTrashed()
 * @method static                                                                                          \Illuminate\Database\Query\Builder|\App\Location withoutTrashed()
 * @mixin \Eloquent
 * @property \CircleLinkHealth\Core\Entities\DatabaseNotification[]|\Illuminate\Notifications\DatabaseNotificationCollection $notifications
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection                     $revisionHistory
 * @method static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Location newModelQuery()
 * @method static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Location newQuery()
 * @method static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Location query()
 * @method static                                                                                                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Location whereClinicalEscalationPhone($value)
 * @property int|null                                                                                                        $clinical_emergency_contact_count
 * @property int|null                                                                                                        $emr_direct_count
 * @property int|null                                                                                                        $notifications_count
 * @property int|null                                                                                                        $providers_count
 * @property int|null                                                                                                        $revision_history_count
 * @property int|null                                                                                                        $user_count
 * @property ChargeableLocationMonthlySummary[]|\Illuminate\Database\Eloquent\Collection                                     $chargeableServiceSummaries
 * @property int|null                                                                                                        $chargeable_service_summaries_count
 * @property \CircleLinkHealth\Customer\Entities\ChargeableService[]|\Illuminate\Database\Eloquent\Collection                $cpmProblemServices
 * @property int|null                                                                                                        $cpm_problem_services_count
 * @property \CircleLinkHealth\Synonyms\Entities\Synonym[]|\Illuminate\Database\Eloquent\Collection                          $synonyms
 * @property int|null                                                                                                        $synonyms_count
 * @method static                                                                                                          \Illuminate\Database\Eloquent\Builder|Location whereColumnOrSynonym($column, $synonym)
 */
class Location extends \CircleLinkHealth\Core\Entities\BaseModel
{
    use HasEmrDirectAddress;
    use Notifiable;
    
    use SoftDeletes;
    use Synonymable;

    /**
     * Mass assignable attributes.
     *
     * @var array
     */
    protected $fillable = [
        'practice_id',
        'external_department_id',
        'is_primary',
        'name',
        'phone',
        'clinical_escalation_phone',
        'fax',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'timezone',
        'postal_code',
        'ehr_login',
        'ehr_password',
    ];

    protected $hidden = [
        'ehr_login',
        'ehr_password',
    ];

    public function availableServiceProcessors(?Carbon $month = null, bool $excludeLocked = true): AvailableServiceProcessors
    {
        $month ??= Carbon::now()->startOfMonth()->startOfDay();

        return AvailableServiceProcessors::push(
            $this->chargeableServiceSummaries
                ->where('chargeable_month', $month)
                ->when($excludeLocked, fn ($q) => $q->where('is_locked', false))
                ->map(function (ChargeableLocationMonthlySummary $summary) {
                    return $summary->chargeableService->processor();
                })
                ->filter()
                ->toArray()
        );
    }

    public function chargeableServiceSummaries()
    {
        return $this->hasMany(ChargeableLocationMonthlySummary::class, 'location_id');
    }

    public function clinicalEmergencyContact()
    {
        return $this->morphToMany(User::class, 'contactable', 'contacts')
            ->withPivot('name')
            ->withTimestamps();
    }

    public function cpmProblemServices()
    {
        return $this->belongsToMany(ChargeableService::class, 'location_problem_services')
            ->using(LocationProblemService::class)
            ->withPivot(['cpm_problem_id']);
    }

    public static function getAllNodes()
    {
        return Location::all()->pluck('name', 'id')->all();
    }

    public static function getAllParents()
    {
        return Location::whereRaw('parent_id IS NULL')->pluck('name', 'id')->all();
    }

    public static function getLocationName($id)
    {
        $q = Location::where('id', '=', $id)->select('name')->first();

        return $q['name'];
    }

    public static function getLocationsForBlog($blogId)
    {
        $q = Location::where('program_id', '=', $blogId)->get();

        return (null == $q)
            ? ''
            : $q;
    }

    public static function getNonRootLocations($parent_location_id = false)
    {
        if ($parent_location_id) {
            $parent_location = Location::where('id', '=', $parent_location_id)->first();
            if ( ! $parent_location) {
                return false;
            }

            return Location::where('parent_id', '=', $parent_location->id)->pluck('name', 'id')->all();
        }

        return Location::where('parent_id', '!=', 'NULL')->pluck('name', 'id')->all();
    }

    public static function getParents($id)
    {
        $l = Location::find($id);

        return Location::where('id', $l->parent_id)->pluck('name', 'id')->all();
    }

    public static function getParentsSubs($id)
    {
        return Location::where('parent_id', '=', $id)->pluck('name', 'id')->all();
    }

    public function isNotSaas()
    {
        return ! $this->isSaas();
    }

    public function isSaas()
    {
        return $this->practice->saas_account_id > 1;
    }

    public function parent()
    {
        return $this->belongsTo(Location::class, 'parent_id');
    }

    public function practice()
    {
        return $this->belongsTo(Practice::class);
    }

    public function program()
    {
        return $this->belongsTo(Practice::class, 'location_id');
    }

    public function providers()
    {
        return $this->belongsToMany(User::class)
            ->ofType('provider');
    }

    public function routeNotificationForMail()
    {
        return optional($this->user()->first())->email;
    }

    public function saasAccount()
    {
        return $this->practice->saasAccount();
    }

    public function saasAccountName()
    {
        $saasAccount = $this->saasAccount->first();

        if ($saasAccount) {
            return $saasAccount->name;
        }

        return 'CircleLink Health';
    }

    public static function setPrimary(Location $loc)
    {
        //set all other practices to 0
        $toRemovePrimaryFrom = $loc->practice->locations()->where('is_primary', 1)->get();

        foreach ($toRemovePrimaryFrom as $location) {
            $location->is_primary = 0;
            $location->save();
        }

        $loc->is_primary = 1;
        $loc->save();

        return $loc;
    }

    public function user()
    {
        return $this->belongsToMany(User::class);
    }
}
