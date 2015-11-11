<?php namespace App\CLH\Repositories;

use App\CLH\DataTemplates\UserConfigTemplate;
use App\CLH\DataTemplates\UserMetaTemplate;
use App\WpUser;
use App\WpUserMeta;
use App\WpBlog;
use App\Role;
use Symfony\Component\HttpFoundation\ParameterBag;

class WpUserRepository {

    public function createNewUser(WpUser $wpUser, ParameterBag $params)
    {
        $wpUser = $wpUser->createNewUser($params->get('user_email'), $params->get('user_pass'));

        $wpUser->load('meta');

        // the basics
        $wpUser->user_nicename = '';
        $wpUser->user_login = $params->get('user_login');
        $wpUser->user_status = $params->get('user_status');
        $wpUser->display_name = $params->get('display_name');
        $wpUser->program_id = $params->get('program_id');
        $wpUser->user_registered = date('Y-m-d H:i:s');

        $this->saveOrUpdateRoles($wpUser, $params);
        $this->saveOrUpdateUserMeta($wpUser, $params);
        $this->updateUserConfig($wpUser, $params);
        $this->saveOrUpdatePrograms($wpUser, $params);

        $wpUser->push();

        return $wpUser;
    }

    public function editUser(WpUser $wpUser, ParameterBag $params)
    {
        // the basics
        $wpUser->user_nicename = '';
        $wpUser->user_login = $params->get('user_login');
        $wpUser->user_status = $params->get('user_status');
        $wpUser->display_name = $params->get('display_name');
        $wpUser->program_id = $params->get('program_id');
        $wpUser->save();

        $this->saveOrUpdateRoles($wpUser, $params);
        $this->saveOrUpdateUserMeta($wpUser, $params);
        $this->updateUserConfig($wpUser, $params);
        $this->saveOrUpdatePrograms($wpUser, $params);

        return $wpUser;
    }


    public function saveOrUpdateUserMeta(WpUser $wpUser, ParameterBag $params)
    {
        $userMetaTemplate = (new UserMetaTemplate())->getArray();

        foreach($userMetaTemplate as $key => $value)
        {
            $userMeta = $wpUser->meta()->firstOrNew([
                'meta_key' => $key,
                'user_id' => $wpUser->ID
            ]);

            if(($params->get($key))) {
                $userMeta->meta_value = $params->get($key);
            } else {
                $userMeta->meta_value = $value;
            }

            $wpUser->meta()->save($userMeta);
        }
    }

    public function saveOrUpdateRoles(WpUser $wpUser, ParameterBag $params)
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

    public function saveOrUpdatePrograms(WpUser $wpUser, ParameterBag $params)
    {
        $userPrograms = $params->get('programs');
        if(!$userPrograms) {
            $userPrograms = array();
        }
        $roleId = $params->get('role');
        $role = Role::find($roleId);

        // first detatch relationship
        $wpUser->programs()->detach();

        $wpBlogs = WpBlog::orderBy('blog_id', 'desc')->lists('blog_id');
        foreach($wpBlogs as $wpBlogId) {
            if (!in_array($wpBlogId, $userPrograms)) {
                $wpUser->meta()->whereMetaKey("wp_{$wpBlogId}_user_level")->delete();
                $wpUser->meta()->whereMetaKey("wp_{$wpBlogId}_capabilities")->delete();
            } else {
                $wpUser->programs()->attach($wpBlogId);
                // user level
                $userLevel = $wpUser->meta()->whereMetaKey("wp_{$wpBlogId}_user_level")->first();
                if($userLevel) {
                    $userLevel->meta_value = "0";
                } else {
                    $userLevel = new WpUserMeta;
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
                    $capabilities = new WpUserMeta;
                    $capabilities->meta_key = "wp_{$wpBlogId}_capabilities";
                    $capabilities->meta_value = serialize(array($role->name => '1'));
                    $capabilities->user_id = $wpUser->ID;
                }
                $capabilities->save();
            }
        }
    }


    public function updateUserConfig(WpUser $wpUser, ParameterBag $params)
    {
        // meta
        $userMeta = WpUserMeta::where('user_id', '=', $wpUser->ID)->lists('meta_value', 'meta_key');

        // config
        $userConfig = (new UserConfigTemplate())->getArray();
        if (isset($userMeta['wp_' . $wpUser->program_id . '_user_config'])) {
            $userConfig = unserialize($userMeta['wp_' . $wpUser->program_id . '_user_config']);
            $userConfig = array_merge((new UserConfigTemplate())->getArray(), $userConfig);
        }

        foreach($userConfig as $key => $value)
        {
            if( ! empty($params->get($key)))
            {
                $userConfig[$key] = $params->get($key);
            }
        }

        $setUserConfig = $wpUser->meta()->whereMetaKey("wp_{$params->get('program_id')}_user_config")->first();
        if($setUserConfig) {
            $setUserConfig->meta_value = serialize($userConfig);
        } else {
            $setUserConfig = new WpUserMeta;
            $setUserConfig->meta_key = "wp_{$params->get('program_id')}_user_config";
            $setUserConfig->meta_value = serialize($userConfig);
            $setUserConfig->user_id = $wpUser->ID;
        }
        $setUserConfig->save();
    }
}
