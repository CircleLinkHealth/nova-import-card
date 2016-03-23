<?php namespace App\CLH\Repositories;

use App\CLH\DataTemplates\UserConfigTemplate;
use App\CLH\DataTemplates\UserMetaTemplate;
use App\User;
use App\UserMeta;
use App\WpBlog;
use App\Role;
use App\CarePlan;
use App\CPRulesPCP;
use App\CPRulesUCP;
use App\Services\CareplanUIService;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\ParameterBag;

class UserRepository {

    public function createNewUser(User $wpUser, ParameterBag $params)
    {
        $wpUser = $wpUser->createNewUser($params->get('user_email'), $params->get('user_pass'));

        $wpUser->load('meta');

        // the basics
        $wpUser->user_nicename = $params->get('user_nicename');
        $wpUser->user_login = $params->get('user_login');
        $wpUser->user_status = $params->get('user_status');
        $wpUser->program_id = $params->get('program_id');
        $wpUser->user_registered = date('Y-m-d H:i:s');
        $wpUser->care_plan_id = $params->get('care_plan_id');
        $wpUser->auto_attach_programs = $params->get('auto_attach_programs');
        $wpUser->save();

        $this->saveOrUpdateRoles($wpUser, $params);
        $this->saveOrUpdateUserMeta($wpUser, $params);
        $this->updateUserConfig($wpUser, $params);
        $this->saveOrUpdatePrograms($wpUser, $params);
        $this->createDefaultCarePlan($wpUser, $params);

        //Add Email Notification
        $sendTo =  ['Plawlor@circlelinkhealth.com','rohanm@circlelinkhealth.com'];
        if (app()->environment('production')) {
            $this->adminEmailNotify( $wpUser, $sendTo );
        }

        $wpUser->push();
        return $wpUser;
    }

    public function editUser(User $wpUser, ParameterBag $params)
    {
        // the basics
        $wpUser->user_nicename = '';
        $wpUser->user_login = $params->get('user_login');
        $wpUser->user_status = $params->get('user_status');
        $wpUser->program_id = $params->get('program_id');
        $wpUser->care_plan_id = $params->get('care_plan_id');
        $wpUser->auto_attach_programs = $params->get('auto_attach_programs');
        $wpUser->save();

        $this->saveOrUpdateRoles($wpUser, $params);
        $this->saveOrUpdateUserMeta($wpUser, $params);
        $this->updateUserConfig($wpUser, $params);
        $this->saveOrUpdatePrograms($wpUser, $params);

        return $wpUser;
    }


    public function saveOrUpdateUserMeta(User $wpUser, ParameterBag $params)
    {
        $userMetaTemplate = (new UserMetaTemplate())->getArray();

        foreach($userMetaTemplate as $key => $value)
        {
            $paramValue = $params->get($key);

            //serialize arrays
            if($paramValue && is_array($paramValue)) {
                $paramValue = serialize($paramValue);
            } else if($value && is_array($value)) {
                $value = serialize($value);
            }

            if(($params->get($key))) {
                $wpUser->setUserAttributeByKey($key, $paramValue);
            } else {
                $wpUser->setUserAttributeByKey($key, $value);
            }
        }
    }

    public function saveOrUpdateRoles(User $wpUser, ParameterBag $params)
    {
        // support for both single or array or roles
        if(!empty($params->get('role'))) {
            $wpUser->roles()->sync(array($params->get('role')));
            return true;
        }

        if(!empty($params->get('roles'))) {
            // support if one role is passed in as a string
            if(!is_array($params->get('roles'))) {
                $roleId = $params->get('roles');
                $wpUser->roles()->sync(array($roleId));
            } else {
                $wpUser->roles()->sync($params->get('roles'));
            }
        } else {
            $wpUser->roles()->sync([]);
        }
    }

    public function saveOrUpdatePrograms(User $wpUser, ParameterBag $params)
    {
        // get selected programs
        $userPrograms = array();
        if($params->get('programs')) { // && ($wpUser->programs->count() > 0)
            $userPrograms = $params->get('programs');
        }
        if($params->get('program_id')) {
            if(!in_array($params->get('program_id'), $userPrograms)) {
                $userPrograms[] = $params->get('program_id');
            }
        }

        //dd($userPrograms);

        // if still empty at this point, no program_id or program param
        if(empty($userPrograms)) {
            return true;
        }

        // set primary program
        $wpUser->program_id = $params->get('program_id');
        $wpUser->save();

        // get role
        $roleId = $params->get('role');
        if($roleId) {
            $role = Role::find($roleId);
        } else {
            // default to participant
            $role = Role::where('name', '=', 'participant')->first();
        }

        // first detatch relationship
        $wpUser->programs()->detach();

        $wpBlogs = WpBlog::orderBy('blog_id', 'desc')->lists('blog_id');
        foreach($wpBlogs as $wpBlogId) {
            if (!in_array($wpBlogId, $userPrograms)) {
                $wpUser->meta()->whereMetaKey("wp_{$wpBlogId}_user_config")->delete();
                $wpUser->meta()->whereMetaKey("wp_{$wpBlogId}_user_level")->delete();
                $wpUser->meta()->whereMetaKey("wp_{$wpBlogId}_capabilities")->delete();
            } else {
                $wpUser->programs()->attach($wpBlogId);
                // user level
                $userLevel = $wpUser->meta()->whereMetaKey("wp_{$wpBlogId}_user_level")->first();
                if($userLevel) {
                    $userLevel->meta_value = "0";
                } else {
                    $userLevel = new UserMeta;
                    $userLevel->meta_key = "wp_{$wpBlogId}_user_level";
                    $userLevel->meta_value = "0";
                    $userLevel->user_id = $wpUser->ID;
                }
                $userLevel->save();

                // capabilities
                $capabilities = $wpUser->meta()->whereMetaKey("wp_{$wpBlogId}_capabilities")->first();
                if($capabilities) {
                    $capabilities->meta_value = serialize(array($role->name => '1'));
                } else {
                    $capabilities = new UserMeta;
                    $capabilities->meta_key = "wp_{$wpBlogId}_capabilities";
                    $capabilities->meta_value = serialize(array($role->name => '1'));
                    $capabilities->user_id = $wpUser->ID;
                }
                $capabilities->save();
            }
        }
    }


    public function updateUserConfig(User $wpUser, ParameterBag $params)
    {
        // meta
        $userMeta = UserMeta::where('user_id', '=', $wpUser->ID)->lists('meta_value', 'meta_key');

        // config
        $userConfig = (new UserConfigTemplate())->getArray();
        if (isset($userMeta['wp_' . $wpUser->program_id . '_user_config'])) {
            $userConfig = unserialize($userMeta['wp_' . $wpUser->program_id . '_user_config']);
            $userConfig = array_merge((new UserConfigTemplate())->getArray(), $userConfig);
        }

        // contact days checkbox formatting
        if($params->get('contact_days')) {
            $contactDays = $params->get('contact_days');
            $contactDaysDelmited = '';
            for($i=0; $i < count($contactDays); $i++){
                $contactDaysDelmited .= (count($contactDays) == $i+1) ? $contactDays[$i] : $contactDays[$i] . ', ';
            }
            $params->add(array('preferred_cc_contact_days' => $contactDaysDelmited));
        }

        foreach($userConfig as $key => $value)
        {
            $paramValue = $params->get($key);

            //serialize arrays
            if($paramValue && is_array($paramValue)) {
                $paramValue = serialize($paramValue);
            } else if($value && is_array($value)) {
                $value = serialize($value);
            }

            if(($paramValue)) {
                $wpUser->setUserAttributeByKey($key, $paramValue);
            } else {
                $wpUser->setUserAttributeByKey($key, $value);
            }
        }
    }


    public function createDefaultCarePlan($user, $params) {

        $program = WpBlog::find($user->program_id);
        if(!$program) {
            return false;
        }
        // just need to add programs default @todo here should get the programs default one to use from programs config
        $carePlan = CarePlan::where('program_id', '=', $program->blog_id)->where('type', '=', 'Program Default')->first();
        if(!$carePlan) {
            return false;
        }

        $user->care_plan_id = $carePlan->id;
        $user->save();

        // populate defaults from careplan
        $carePlan->build();
        foreach($carePlan->careSections as $careSection) {
            // add parent items to each section
            $careSection->carePlanItems = $carePlan->carePlanItems()
                ->where('section_id', '=', $careSection->id)
                ->where('parent_id', '=', 0)
                ->orderBy('ui_sort', 'asc')
                ->with(array('children' => function ($query) {
                    $query->orderBy('ui_sort', 'asc');
                }))
                ->get();
            // user defaults
            if ($careSection->carePlanItems->count() > 0) {
                foreach ($careSection->carePlanItems as $carePlanItem) {
                    // parents
                    $carePlan->setCareItemUserValue($user, $carePlanItem->careItem->name, $carePlanItem->meta_value);
                    // children
                    if ($carePlanItem->children->count() > 0) {
                        foreach ($carePlanItem->children as $carePlanItemChild) {
                            $carePlan->setCareItemUserValue($user, $carePlanItemChild->careItem->name, $carePlanItemChild->meta_value);
                        }
                    }
                }
            }
        }


        /*
         * OLD RULES_* careplan structure
        // get providers
        $sections = CPRulesPCP::where('prov_id', '=' , $wpUser->program_id)->get();
        if(count($sections) > 0) {
            foreach ($sections as $section) {
                $sectionData = (new CareplanUIService)->getCareplanSectionData($wpUser->program_id, $section->section_text, $wpUser);
                if (empty($sectionData)) {
                    return false;
                }
                $parentItems = $sectionData['items'];
                $itemData = $sectionData['sub_meta'];
                foreach ($parentItems as $parentItemName => $parentItemInfo) {
                    //echo '<h2>' . $parentItemName . '</h2>';
                    foreach ($parentItemInfo as $child1Key => $child1Info) {
                        //echo '<h3>' . $child1Key . '</h3>';
                        // does it have children?
                        if (isset($itemData[$parentItemName][$child1Key])) {
                            // HAS CHILDREN ITEMS
                        } else if (isset($itemData[$parentItemName][0][$child1Key]['items_id'])) {
                            // SINGLETON, HAS NO CHILDREN
                        }
                        // ensure status is set
                        if (strlen($child1Info['status']) < 3) {
                            $child1Info['status'] = 'Inactive';
                        }
                        $item_checkbox_key = 'CHECK_STATUS|' . $itemData[$parentItemName][0][$child1Key]['items_id'] . '|' . $itemData[$parentItemName][0][$child1Key]['items_id'] . "|status";
                        //echo "Adding to UCP! meta_key = status meta_value = " . $child1Info['status'] . "<br><br>";
                        $newUCP = new CPRulesUCP;
                        $newUCP->items_id = $itemData[$parentItemName][0][$child1Key]['items_id'];
                        $newUCP->user_id = $wpUser->ID;
                        $newUCP->meta_key = 'status';
                        $newUCP->meta_value = $child1Info['status'];
                        $newUCP->save();
                        if (isset($itemData[$parentItemName][$child1Key])) {
                            foreach ($itemData[$parentItemName][$child1Key] as $child2Key => $child2Info) {
                                // item heading
                                //echo '<br><strong>' . $child2Key . '</strong><br>';
                                // show info
                                foreach ($child2Info as $key => $value) {
                                    //echo $key . ' :: ' . $value . '<br>';
                                }
                                // set null to empty string
                                if (strtolower($child2Info['ui_default']) == 'null') {
                                    $child2Info['ui_default'] = '';
                                }
                                // add to ucp
                                //echo "Adding to UCP! meta_key = value meta_value = " . $child2Info['ui_default'];
                                $newUCP = new CPRulesUCP;
                                $newUCP->items_id = $child2Info['items_id'];
                                $newUCP->user_id = $wpUser->ID;
                                $newUCP->meta_key = 'value';
                                $newUCP->meta_value = $child2Info['ui_default'];
                                $newUCP->save();
                            }
                        }

                    }
                }
            }
        }
        */
    }

    public function adminEmailNotify(User $user, $recipients){

//   Template:
//        From: CircleLink Health
//        Sent: Tuesday, January 5, 10:11 PM
//        Subject: [Site Name] New User Registration!
//        To: Phil Lawlor       Plawlor@circlelinkhealth.com
//            Linda Warshavsky  lindaw@circlelinkhealth.com
//
//New user registration on Dr Daniel A Miller, MD: Username: WHITE, MELDA JEAN [834] E-mail: test@gmail.com

        $email_view = 'emails.newpatientnotify';
        $program = WpBlog::find($user->blogId());
        $program_name = $program->display_name;
        $email_subject = '[' . $program_name . '] New '. ucwords($user->role()) .' Registration!';
        $data = array(
            'patient_name' => $user->getFullNameAttribute(),
            'patient_id' => $user->ID,
            'patient_email' => $user->getEmailForPasswordReset(),
            'program' => $program_name
        );

        Mail::send($email_view, $data, function($message) use ($recipients,$email_subject) {
            $message->from('no-reply@careplanmanager.com', 'CircleLink Health');
            $message->to($recipients)->subject($email_subject);
        });
    }
}
