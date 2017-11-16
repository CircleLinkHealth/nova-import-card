<?php namespace App;

use App\Models\Ehr;
use App\Traits\HasSettings;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Practice
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
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CarePlanTemplate[] $careplan
 * @property-read \App\Models\Ehr|null $ehr
 * @property-read mixed $formatted_name
 * @property-read mixed $primary_location_id
 * @property-read mixed $subdomain
 * @property-read \App\User|null $lead
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Location[] $locations
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CPRulesPCP[] $pcp
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Settings[] $settings
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User[] $users
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
class Practice extends \App\BaseModel
{
    use HasSettings,
        SoftDeletes;

    protected $fillable = [
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

    public function getInvoiceRecipients()
    {
        return $this->users()->where('send_billing_reports', '=', true)->get();
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'practice_user', 'program_id', 'user_id')
            ->withPivot('role_id', 'has_admin_rights', 'send_billing_reports');
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

    public function pcp()
    {
        return $this->hasMany('App\CPRulesPCP', 'prov_id', 'id');
    }

    public function careplan()
    {
        return $this->hasMany('App\CarePlanTemplate', 'patient_id');
    }

    public function getPrimaryLocationIdAttribute()
    {
        $loc = $this->locations->where('is_primary', '=', true)->first();

        return $loc
            ? $loc->id
            : null;
    }

    public function primaryLocation()
    {
        return $this->locations->where('is_primary', '=', true)->first();
    }

    public function locationId()
    {
        return $this->location_id;
    }

    public function lead()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function enrollmentByProgram(
        Carbon $start,
        Carbon $end
    )
    {

        $patients = Patient::whereHas('user', function ($q) {

            $q->where('program_id', $this->id);
        })
            ->whereNotNull('ccm_status')
            ->get();

        $data = [

            'withdrawn' => 0,
            'paused' => 0,
            'added' => 0,

        ];

        foreach ($patients as $patient) {
            if ($patient->created_at > $start->toDateTimeString() && $patient->created_at <= $end->toDateTimeString()) {
                $data['added']++;
            }

            if ($patient->date_withdrawn > $start->toDateTimeString() && $patient->date_withdrawn <= $end->toDateTimeString()) {
                $data['withdrawn']++;
            }

            if ($patient->date_paused > $start->toDateTimeString() && $patient->date_paused <= $end->toDateTimeString()) {
                $data['paused']++;
            }
        }

        return $data;
    }

    public function getAddress()
    {

        $primary = $this->locations()->where('is_primary', 1)->first();

        if (is_null($primary)) {
            $primary = $this->locations()->first();
        }

        return [

            'line1' => $primary->address_line_1 . ' ' . $primary->address_line_2,
            'line2' => $primary->city . ', ' . $primary->state . ' ' . $primary->postal_code,

        ];
    }

    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    public function getSubdomainAttribute()
    {
        return explode('.', $this->domain)[0];
    }

    public function ehr()
    {
        return $this->belongsTo(Ehr::class);
    }

    public function scopeActive($q)
    {

        return $q->whereActive(1);
    }

    public function cpmSettings()
    {
        return $this->settings->isEmpty()
            ? $this->syncSettings(new Settings())
            : $this->settings->first();
    }
}
