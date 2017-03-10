<?php namespace App;

use App\Models\Ehr;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Practice extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'name',
        'display_name',
        'federal_tax_id',
        'user_id',
        'same_clinical_contact',
        'same_ehr_login',
        'sms_marketing_number',
        'weekly_report_recipients',
        'auto_approve_careplans',
        'send_alerts',
    ];

    public static function getProviders($practiceId)
    {
        $providers = User::whereHas('practices', function ($q) use
        (
            $practiceId
        ) {
            $q->where('id', '=', $practiceId);
        })->whereHas('roles', function ($q) {
            $q->where('name', '=', 'provider');
        })->get();

        return $providers;
    }

    public static function getNonCCMCareCenterUsers($practiceId)
    {
        $providers = User::whereHas('practices', function ($q) use
        (
            $practiceId
        ) {
            $q->where('id', '=', $practiceId);
        })->whereHas('roles', function ($q) {
            $q->where('name', '=', 'no-ccm-care-center');
        })->get();

        return $providers;
    }

    public static function getCareCenterUsers($practiceId)
    {
        $providers = User::whereHas('practices', function ($q) use
        (
            $practiceId
        ) {
            $q->where('id', '=', $practiceId);
        })->whereHas('roles', function ($q) {
            $q->where('name', '=', 'care-center');
        })->get();

        return $providers;
    }

    public static function getItemsForParent(
        $item,
        $blogId
    ) {
        $categories = [];
        //PCP has the sections for each provider, get all sections for the user's blog
        $pcp = CPRulesPCP::where('prov_id', '=', $blogId)->where('status', '=', 'Active')->where('section_text',
            $item)->first();
        //Get all the items for each section
        if ($pcp) {
            $items = CPRulesItem::where('pcp_id', $pcp->pcp_id)->where('items_parent', 0)->pluck('items_id')->all();
            for ($i = 0; $i < count($items); $i++) {
                //get id's of all lifestyle items that are active for the given user
                $item_for_user[$i] = CPRulesUCP::where('items_id', $items[$i])->where('meta_value',
                    'Active')->where('user_id', $blogId)->first();
                if ($item_for_user[$i] != null) {
                    //Find the items_text for the one's that are active
                    $user_items = CPRulesItem::find($item_for_user[$i]->items_id);
                    $categories[] = [
                        'name'         => $user_items->items_text,
                        'items_id'     => $user_items->items_id,
                        'section_text' => $item,
                        'items_text'   => $user_items->items_text,
                    ];
                }
            }
            if (count($categories) > 0) {
                return $categories;
            } else {
                return false;
            }
        }
    }

    public function getCountOfUserTypeAtPractice($role)
    {

        $id = $this->id;

        return User
            ::where('user_status', 1)
            ->whereProgramId($this->id)
            ->whereHas('roles', function ($q) use
            (
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

    public function users()
    {
        return $this->belongsToMany(User::class, 'practice_user', 'program_id', 'user_id')
            ->withPivot('role_id', 'has_admin_rights', 'send_billing_reports');
    }

    public function locations()
    {
        return $this->hasMany(Location::class);
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

    public function getSubdomainAttribute()
    {
        return explode('.', $this->domain)[0];
    }

    public function ehr()
    {
        return $this->belongsTo(Ehr::class);
    }
}
