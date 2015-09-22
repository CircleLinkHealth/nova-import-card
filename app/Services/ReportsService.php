<?php namespace App\Services;

use App\CPRulesItem;
use App\CPRulesPCP;
use App\CPRulesUCP;
use App\Location;
use App\Observation;
use App\WpBlog;
use App\WpUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

Class ReportsService
{

    public function progress($id)
    {

        $user = WpUser::find($id);

        $progress = array();
        $userHeader = array();

        $trackingChanges = array();
        $trackingChanges['Section'] = 'Tracking Changes:';
        $medications = array();
        $medications['Section'] = 'Taking your medications?:';

        //USER HEADER:

        $userHeader['date'] = Carbon::now()->toDateString();
        $userHeader['Patient_Name'] = $user->display_name;
        $userConfig = $user->userConfig();
        $userHeader['Patient_Phone'] = $userConfig['study_phone_number'];
        $provider = WpUser::find($userConfig['lead_contact']);
        $providerConfig = $provider->userConfig();
        $userHeader['Patient_Phone'] = $providerConfig['study_phone_number'];
        $userHeader['Provider_Name'] = $provider->display_name;
        $userHeader['Provider_Phone'] = $providerConfig['study_phone_number'];
        $userHeader['Clinic_Name'] = Location::getLocationName($providerConfig['preferred_contact_location']);

        //TAKING YOUR MEDICATIONS

        //$medications_pcp = CPRulesPCP::where('prov_id', '=', $user->blogId())->where('status', '=', 'Active')->where('section_text', 'Medications to Monitor')->where('items_parent', 0)->lists('pcp_id');
        //dd($medications_pcp);

        //TRACKING CHANGES
        $obs_keys = array('Blood_Sugar','Blood_Pressure','Weight','Cigarettes');
        foreach ($obs_keys as $obs_key) {

            $trackingChanges['data'] = DB::table('observations')
                ->where('user_id',308)
                ->where('obs_key',$obs_key)
                ->select('user_id', 'obs_date','obs_key')->get();
        }

        $progress['Progress_Report'][] = $userHeader;
        //$progress['Progress_Report'][] = $trackingChanges;
        $progress['Progress_Report'][] = $medications;
        return json_encode($progress);
    }

    //CarePlan API

    public function careplan($id){

        //WE ARE TREATING
        $user = WpUser::find($id);
        $treating = array();
        $treating['Section'] = 'We Are Treating:';

        //PCP has the sections for each provider, get all sections for the user's blog
        $pcp = CPRulesPCP::where('prov_id', '=', $user->blogId())->where('status', '=', 'Active')->where('section_text', 'Diagnosis / Problems to Monitor')->lists('pcp_id');
        //Get all the items for each section
        $tracking_items_ids = CPRulesItem::where('pcp_id', $pcp)->where('items_parent', 0)->lists('items_id');
        for($i = 0; $i < count($tracking_items_ids); $i++){
            //get id's of all items that are active for the given user
            $item_for_user[$i] = CPRulesUCP::where('items_id', $tracking_items_ids[$i])->where('meta_value', 'Active')->where('user_id',$user->ID)->first();
            if($item_for_user[$i] != null){
                //Find the items_text for the one's that are active
                $user_items = CPRulesItem::find($item_for_user[$i]->items_id);
                $treating['data'][] = ['name' => $user_items->items_text];
            }
        }//dd(json_encode($treating));

    }
}