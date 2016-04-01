<?php

use App\User;
use App\Activity;
use App\PageTimer;
use App\Services\ActivityService;
use App\Services\RulesService;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;


class PageTimeToActivityReprocess extends Seeder {

    public function run()
    {
        $pageTimes = PageTimer::where('rule_id', '=', '0')->where('start_time', '>', '2016-03-23 00:00:00')->get();
        $a = 0;
        if(!empty($pageTimes)) {
            foreach($pageTimes as $pageTime) {
                //echo $pageTime->id.'-'.$pageTime->start_time.PHP_EOL;

                // make sure pageTime doesnt already have an activity
                $existingActivity = Activity::where('page_timer_id','=',$pageTime->id)->first();
                if($existingActivity) {
                    //echo 'skipping... has existing activity'.PHP_EOL;
                    continue 1;
                }

                // check params to see if rule exists
                $params = array();

                //provider
                $provider = User::find( $pageTime->provider_id );
                if(!$provider) {
                    //echo 'skipping... no provider found'.PHP_EOL;
                    continue 1;
                }

                // provider role param
                $params['role'] = '';
                $role = $provider->roles()->first();
                if($role) {
                    $params['role'] = $role->name;
                }

                // activity param
                $params['activity'] = $pageTime->activity_type;
                //$params['program_id'] = $pageTime->program_id;
                //$params = array('role' => 'Provider', 'activity' => 'Patient Overview');

                // check against rules and add activity if passes
                $rulesService = new RulesService;
                $ruleActions = $rulesService->getActions($params, 'ATT');

                if($ruleActions) {
                    $activiyParams = array();
                    $activiyParams['type'] = $params['activity'];
                    $activiyParams['provider_id'] = $pageTime->provider_id;
                    $activiyParams['performed_at'] = $pageTime->start_time;
                    $activiyParams['duration'] = $pageTime->duration;
                    $activiyParams['duration_unit'] = 'seconds';
                    $activiyParams['patient_id'] = $pageTime->patient_id;
                    $activiyParams['logged_from'] = 'pagetimer';
                    $activiyParams['logger_id'] = $pageTime->provider_id;
                    $activiyParams['page_timer_id'] = $pageTime->id;
                    $activiyParams['meta'] = array('meta_key' => 'comment', 'meta_value' => 'logged from pagetimer');
                    echo 'PageTime id = '. $pageTime->id.' - create new activity! matched rule id='.$ruleActions[0]->id. ' w/ rules ' . serialize($params) . PHP_EOL.PHP_EOL;
                    $a++;
                    //var_dump($params);
                    //dd($activiyParams);
                    //dd('create new activity!');
                    /*
                    // if rule exists, create activity
                    $activityId = Activity::createNewActivity($activiyParams);

                    $activityService = new ActivityService;
                    $result = $activityService->reprocessMonthlyActivityTime($pageTime->patient_id);
                    */
                }

                /*
                // update pagetimer
                $pageTime->processed = 'Y';
                $pageTime->rule_params = serialize($params);
                $pageTime->rule_id = ($ruleActions) ? $ruleActions[0]->id : '';
                $pageTime->save();
                */
                //return true;
            }
            echo "Total activities generated: " . $a.PHP_EOL;
        }
    }

}