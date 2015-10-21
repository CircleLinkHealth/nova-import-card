<?php namespace App\CLH\Repositories;

use App\WpUser;
use App\WpUserMeta;
use Symfony\Component\HttpFoundation\ParameterBag;

class WpUserRepository {

    public function createNewUser(WpUser $wpUser, ParameterBag $params)
    {
        $wpUser->createNewUser($params->get('user_email'), $params->get('user_pass'));

        $wpUser = WpUser::with('meta')->find($wpUser->ID);

        $wpUser->user_nicename = $params->get('user_nicename');
        $wpUser->program_id = $params->get('primary_blog');

        if(!empty($params->get('roles'))) {
            $wpUser->roles()->sync($params->get('roles'));
        } else {
            $wpUser->roles()->sync([]);
        }

        // save meta
        $userMetaTemplate = $wpUser->userMetaTemplate();
        foreach($userMetaTemplate as $key => $value) {
            $userMeta = new WpUserMeta;
            $userMeta->user_id = $wpUser->ID;
            $userMeta->meta_key = $key;
            if($params->get($key)) {
                $userMeta->meta_value = $params->get($key);
            } else {
                $userMeta->meta_value = $value;
            }
            $wpUser->meta()->save($userMeta);
        }

        // update role / capabilities (wp)
        $input = $params->get('role');
        $capabilities = new WpUserMeta;
        $capabilities->meta_key = 'wp_' . $params->get('primary_blog') . '_capabilities';
        $capabilities->meta_value = serialize(array($input => '1'));
        $capabilities->user_id = $wpUser->ID;
        $capabilities->save();
        $capabilities = new WpUserMeta;
        $capabilities->meta_key = 'wp_' . $params->get('primary_blog') . '_user_level';
        $capabilities->meta_value = '0';
        $capabilities->user_id = $wpUser->ID;
        $capabilities->save();

        // update user config
        $userConfigTemplate = $wpUser->userConfigTemplate();
        foreach($userConfigTemplate as $key => $value) {
            $input = $params->get($key);
            if(!empty($input)) {
                $userConfigTemplate[$key] = $input = $params->get($key);
            }
        }
        $userConfig = $wpUser->meta->where('user_id', '=', $wpUser->ID)->where('meta_key', '=', 'wp_' . $params->get('primary_blog') . '_user_config')->first();
        if($userConfig) {
            $userConfig->meta_value = serialize($userConfigTemplate);
        } else {
            $userConfig = new WpUserMeta;
            $userConfig->meta_key = 'wp_' . $params->get('primary_blog') . '_user_config';
            $userConfig->meta_value = serialize($userConfigTemplate);
            $userConfig->user_id = $wpUser->ID;
        }
        $userConfig->save();

        $wpUser->push();

        return $wpUser;
    }

    public function editUser(WpUser $wpUser, ParameterBag $params)
    {
        // the basics
        $wpUser->user_nicename = $params->get('user_nicename');
        $wpUser->display_name = $params->get('display_name');
        $wpUser->program_id = $params->get('primary_blog');
        $wpUser->save();

        // roles
        if(($params->get('roles'))) {
            $wpUser->roles()->sync($params->get('roles'));
        } else {
            $wpUser->roles()->sync(array());
        }

        // save meta
        $userMetaTemplate = $wpUser->userMetaTemplate();
        foreach($userMetaTemplate as $key => $value) {
            $userMeta = $wpUser->meta()->where('meta_key', $key)->first();
            if(!$userMeta) {
                $userMeta = new WpUserMeta;
            }
            $userMeta->user_id = $wpUser->ID;
            $userMeta->meta_key = $key;
            if(($params->get($key))) {
                $userMeta->meta_value = $params->get($key);
            } else {
                $userMeta->meta_value = $value;
            }
            $wpUser->meta()->save($userMeta);
        }

        // update role
        $input = $params->get('role');
        if(!empty($input)) {
            $capabilities = $wpUser->meta()->where('user_id', '=', $wpUser->ID)->where('meta_key', '=', 'wp_' . $wpUser->blogId() . '_capabilities')->first();
            if($capabilities) {
                $capabilities->meta_value = serialize(array($input => '1'));
            } else {
                $capabilities = new WpUserMeta;
                $capabilities->meta_key = 'wp_' . $wpUser->blogId() . '_capabilities';
                $capabilities->meta_value = serialize(array($input => '1'));
                $capabilities->user_id = $wpUser->ID;
            }
            $capabilities->save();
        }

        // update user config
        $userConfigTemplate = $wpUser->userConfigTemplate();
        foreach($userConfigTemplate as $key => $value) {
            if(($params->get($key))) {
                $userConfigTemplate[$key] = $params->get($key);
            }
        }
        $userConfig = $wpUser->meta()->where('user_id', '=', $wpUser->ID)->where('meta_key', '=', 'wp_' . $wpUser->blogId() . '_user_config')->first();
        if($userConfig) {
            $userConfig->meta_value = serialize($userConfigTemplate);
        } else {
            $userConfig = new WpUserMeta;
            $userConfig->meta_key = 'wp_' . $wpUser->blogId() . '_user_config';
            $userConfig->meta_value = serialize($userConfigTemplate);
            $userConfig->user_id = $id;
        }
        $userConfig->save();

        return $wpUser;
    }

}
