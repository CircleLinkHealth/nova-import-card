<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 01/28/2018
 * Time: 11:03 PM
 */

namespace App\View\Composers\SAAS\Admin;


use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ManageInternalUser extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function boot()
    {
        View::composer(['saas.admin.user.create'], function ($view) {
            $data = collect($view->getData());

            $usernameField = $data->has('editedUser') ? $data->get('editedUser')->getUser()->username : '';
            $emailField = $data->has('editedUser') ? $data->get('editedUser')->getUser()->email : '';
            $firstNameField = $data->has('editedUser') ? $data->get('editedUser')->getUser()->first_name : '';
            $lastNameField = $data->has('editedUser') ? $data->get('editedUser')->getUser()->last_name : '';
            $practicesField = $data->has('editedUser') ? $data->get('editedUser')->getPractices() : '';
            $roleField = $data->has('editedUser') ? $data->get('editedUser')->getRole() : '';


            $view->with(compact([
                'usernameField',
                'emailField',
                'firstNameField',
                'lastNameField',
                'practicesField',
                'roleField',
            ]));
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}