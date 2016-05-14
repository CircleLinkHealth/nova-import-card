<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Program extends Model {

    use SoftDeletes;

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mysql_no_prefix';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'wp_blogs';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'blog_id';

	//
    public function pcp(){
        return $this->hasMany('App\CPRulesPCP', 'prov_id', 'blog_id');
    }

    public function careplan() {
        return $this->hasMany('App\CarePlanTemplate', 'patient_id');
    }

    public function users() {
        return $this->belongsToMany('App\User', 'lv_program_user', 'program_id', 'user_id');
    }

    public function location()
    {
        return $this->belongsTo('App\Location', 'location_id');
    }

    public static function getProviders($blogId){
        $providers = User::whereHas('programs', function ($q) use ($blogId) {
            $q->where('blog_id', '=', $blogId);
        })->whereHas('roles', function ($q) {
            $q->where('name', '=', 'provider');
        })->get();
        return $providers;
    }

    public static function getNonCCMCareCenterUsers($blogId){
        $providers = User::whereHas('programs', function ($q) use ($blogId) {
            $q->where('blog_id', '=', $blogId);
        })->whereHas('roles', function ($q) {
            $q->where('name', '=', 'no-ccm-care-center');
        })->get();
        return $providers;
    }

    public static function getCareCenterUsers($blogId){
        $providers = User::whereHas('programs', function ($q) use ($blogId) {
            $q->where('blog_id', '=', $blogId);
        })->whereHas('roles', function ($q) {
            $q->where('name', '=', 'care-center');
        })->get();
        return $providers;
    }

    public function locationId() {
        /*
        $location = \DB::select("select * from wp_".$this->blog_id."_options where option_name = 'location_id'", []);
        return $location[0]->option_value;
        */
        return $this->location_id;
    }

    public static function getItemsForParent($item, $blogId)
    {
        $categories = array();
        //PCP has the sections for each provider, get all sections for the user's blog
        $pcp = CPRulesPCP::where('prov_id', '=', $blogId)->where('status', '=', 'Active')->where('section_text', $item)->first();
        //Get all the items for each section
        if ($pcp) {
            $items = CPRulesItem::where('pcp_id', $pcp->pcp_id)->where('items_parent', 0)->lists('items_id')->all();
            for ($i = 0; $i < count($items); $i++) {
                //get id's of all lifestyle items that are active for the given user
                $item_for_user[$i] = CPRulesUCP::where('items_id', $items[$i])->where('meta_value', 'Active')->where('user_id', $blogId)->first();
                if ($item_for_user[$i] != null) {
                    //Find the items_text for the one's that are active
                    $user_items = CPRulesItem::find($item_for_user[$i]->items_id);
                    $categories[] = [
                        'name' => $user_items->items_text,
                        'items_id' => $user_items->items_id,
                        'section_text' => $item,
                        'items_text' => $user_items->items_text
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
    }
