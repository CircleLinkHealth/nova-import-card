<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use CircleLinkHealth\Core\Entities\BaseModel;
use App\CareAmbassadorLog;
use App\CLH\Helpers\StringManipulation;
use App\EnrolleeCustomFilter;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Ehr;
use CircleLinkHealth\Customer\Entities\Patient;
use App\Traits\HasChargeableServices;
use App\Traits\HasSettings;
use App\Traits\SaasAccountable;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Entities\Settings;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

/**
 * CircleLinkHealth\Customer\Entities\Practice.
 *
 * @property int $id
 * @property int|null $ehr_id
 * @property int|null $user_id
 * @property string $name
 * @property string|null $display_name
 * @property int $active
 * @property float $clh_pppm
 * @property int $term_days
 * @property string|null $federal_tax_id
 * @property int|null $same_ehr_login
 * @property int|null $same_clinical_contact
 * @property int $auto_approve_careplans
 * @property int $send_alerts
 * @property string|null $weekly_report_recipients
 * @property string $invoice_recipients
 * @property string $bill_to_name
 * @property string|null $external_id
 * @property string $outgoing_phone_number
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string|null $deleted_at
 * @property string|null $sms_marketing_number
 * @property \App\CarePlanTemplate[]|\Illuminate\Database\Eloquent\Collection $careplan
 * @property \CircleLinkHealth\Customer\Entities\Ehr|null $ehr
 * @property mixed $formatted_name
 * @property mixed $primary_location_id
 * @property mixed $subdomain
 * @property \CircleLinkHealth\Customer\Entities\User|null $lead
 * @property \CircleLinkHealth\Customer\Entities\Location[]|\Illuminate\Database\Eloquent\Collection $locations
 * @property \App\CPRulesPCP[]|\Illuminate\Database\Eloquent\Collection $pcp
 * @property \CircleLinkHealth\Customer\Entities\Settings[]|\Illuminate\Database\Eloquent\Collection $settings
 * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection $users
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice active()
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Practice onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereAutoApproveCareplans($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereBillToName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereClhPppm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereEhrId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereFederalTaxId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereInvoiceRecipients($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereOutgoingPhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereSameClinicalContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereSameEhrLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereSendAlerts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereSmsMarketingNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereTermDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereWeeklyReportRecipients($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Practice withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Practice withoutTrashed()
 * @mixin \Eloquent
 */
class Practice extends BaseModel implements HasMedia
{
    use HasMediaTrait,
        HasChargeableServices,
        HasSettings,
        SaasAccountable,
        SoftDeletes,
        Notifiable;

    protected $fillable = [
        'saas_account_id',
        'name',
        'display_name',
        'active',
        'federal_tax_id',
        'user_id',
        'same_clinical_contact',
        'clh_pppm',
        'same_ehr_login',
        'sms_marketing_number',
        'weekly_report_recipients',
        'invoice_recipients',
        'bill_to_name',
        'auto_approve_careplans',
        'send_alerts',
        'outgoing_phone_number',
        'term_days',
    ];

    public function careAmbassadorLogs()
    {
        return $this->belongsToMany(CareAmbassadorLog::class);
    }

    public function careplan()
    {
        return $this->hasMany('App\CarePlanTemplate', 'patient_id');
    }

    public function cpmSettings()
    {
        return $this->settings->isEmpty()
            ? $this->syncSettings(new Settings())
            : $this->settings->first();
    }

    public function ehr()
    {
        return $this->belongsTo(Ehr::class);
    }

    public function enrolleeCustomFilters()
    {
        return $this->belongsToMany(EnrolleeCustomFilter::class, 'practice_enrollee_filters', 'practice_id',
            'filter_id');
    }

    public function enrollmentByProgram(
        Carbon $start,
        Carbon $end
    ) {
        $patients = Patient::whereHas('user', function ($q) {
            $q->where('program_id', $this->id);
        })
                           ->whereNotNull('ccm_status')
                           ->get();

        $data = [
            'withdrawn' => 0,
            'paused'    => 0,
            'added'     => 0,
        ];

        foreach ($patients as $patient) {
            if ($patient->created_at > $start->toDateTimeString() && $patient->created_at <= $end->toDateTimeString()) {
                ++$data['added'];
            }

            if ($patient->date_withdrawn > $start->toDateTimeString() && $patient->date_withdrawn <= $end->toDateTimeString()) {
                ++$data['withdrawn'];
            }

            if ($patient->date_paused > $start->toDateTimeString() && $patient->date_paused <= $end->toDateTimeString()) {
                ++$data['paused'];
            }
        }

        return $data;
    }

    /**
     * @return \App\PracticeEnrollmentTips|\Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function enrollmentTips()
    {
        return $this->hasOne('App\PracticeEnrollmentTips', 'practice_id');
    }

    /**
     * @throws \Exception
     *
     * @return array
     */
    public function getAddress()
    {
        $primary = $this->locations()->where('is_primary', 1)->first();

        if (is_null($primary)) {
            $primary = $this->locations()->first();
        }

        if (is_null($primary)) {
            throw new \Exception('This Practice does not have a location.', 500);
        }

        return [
            'line1' => $primary->address_line_1 . ' ' . $primary->address_line_2,
            'line2' => $primary->city . ', ' . $primary->state . ' ' . $primary->postal_code,
        ];
    }

    public function getCountOfUserTypeAtPractice($role)
    {
        $id = $this->id;

        return User
            ::where('user_status', 1)
            ->whereProgramId($this->id)
            ->whereHas('roles', function ($q) use (
                $role
            ) {
                $q->whereName($role);
            })
            ->count();
    }

    public function getFormattedNameAttribute()
    {
        return ucwords($this->display_name);
    }

    public function getInvoiceRecipients()
    {
        return $this->users()->where('send_billing_reports', '=', true)->get();
    }

    public function getInvoiceRecipientsArray()
    {
        return array_values(array_filter(array_map('trim', explode(',', $this->invoice_recipients))));
    }

    /**
     * Get phone number in this format xxx-xxx-xxxx.
     *
     * @return string
     */
    public function getNumberWithDashesAttribute()
    {
        return (new StringManipulation())->formatPhoneNumber($this->outgoing_phone_number);
    }

    public function getPrimaryLocationIdAttribute()
    {
        $loc = $this->locations->where('is_primary', '=', true)->first();

        return $loc
            ? $loc->id
            : null;
    }

    public static function getProviders($practiceId)
    {
        $providers = User::whereHas('practices', function ($q) use (
            $practiceId
        ) {
            $q->where('id', '=', $practiceId);
        })->whereHas('roles', function ($q) {
            $q->where('name', '=', 'provider');
        })->get();

        return $providers;
    }

    public function getSubdomainAttribute()
    {
        return explode('.', $this->domain)[0];
    }

    public function getWeeklyReportRecipientsArray()
    {
        return array_map('trim', explode(',', $this->weekly_report_recipients));
    }

    public function isTwilioEnabled()
    {
        $settings = $this->cpmSettings();

        return boolval($settings->twilio_enabled);
    }

    public function lead()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function locationId()
    {
        return $this->location_id;
    }

    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    public function nurses()
    {
        return $this->users()->whereHas('roles', function ($q) {
            $q->where('name', '=', 'care-center')->orWhere('name', 'registered-nurse');
        });
    }

    public function patients()
    {
        return $this->users()->ofType('participant')->whereHas('patientInfo');
    }

    public function pcp()
    {
        return $this->hasMany('App\CPRulesPCP', 'prov_id', 'id');
    }

    public function primaryLocation()
    {
        return $this->locations->where('is_primary', '=', true)->first();
    }

    public function providers()
    {
        return Practice::getProviders($this->id);
    }

    public function scopeActive($q)
    {
        return $q->whereActive(1);
    }

    public function scopeActiveBillable($q)
    {
        if (app()->environment(['local', 'staging', 'testing'])) {
            return $q->whereActive(1);
        }

        return $q->whereActive(1)
                 ->whereNotIn('name', ['demo', 'testdrive', 'mdally-demo']);
    }

    public function scopeAuthUserCanAccess($q, $softwareOnly = false)
    {
        $user = auth()->user();
        if ($softwareOnly) {
            $roleIds = Role::getIdsFromNames(['software-only']);
            $softwareOnlyPractices = $user->practices(true, true, $roleIds)->pluck('id')->all();
            return $q->whereIn('id', $softwareOnlyPractices);
        } else {
            return $q->whereIn('id', $user->practices->pluck('id')->all());
        }

    }

    public function scopeAuthUserCannotAccess($q)
    {
        return $q->whereNotIn('id', auth()->user()->practices->pluck('id')->all());
    }

    public function scopeEnrolledPatients($builder)
    {
        return $builder->with([
            'patients' => function ($q) {
                $q->with([
                    'patientInfo',
                    'activities',
                ])
                  ->whereHas('patientInfo', function ($patient) {
                      $patient->where('ccm_status', Patient::ENROLLED);
                  });
            },
        ]);
    }

    public function setDirectMailCareplanApprovalReminders($bool)
    {
        $this->cpmSettings->dm_careplan_approval_reminders = $bool;
        $this->cpmSettings->save();
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'practice_role_user', 'program_id', 'user_id')
                    ->withPivot('role_id', 'send_billing_reports')
                    ->withTimestamps();
    }
}
