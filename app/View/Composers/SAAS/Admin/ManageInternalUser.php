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
        View::composer(['saas.admin.user.manage'], function ($view) {
            $autoAttachPrograms = $showNurseInfo = $usernameField = $emailField = $firstNameField = $lastNameField = $practicesField = $roleField = $internalUserId = $nurseInfo = '';

            $data = collect($view->getData());

            if ($data->has('editedUser')) {
                $editedUser = $data->get('editedUser')->getUser();

                $autoAttachPrograms = $editedUser->auto_attach_programs
                    ? 'checked'
                    : '';
                $usernameField      = $editedUser->username;
                $emailField         = $editedUser->email;
                $firstNameField     = $editedUser->getFirstName();
                $lastNameField      = $editedUser->getLastName();
                $practicesField     = $data->get('editedUser')->getPractices();
                $roleField          = $data->get('editedUser')->getRole();
                $internalUserId     = $editedUser->id;

                //Removing since we are not including this in the SAAS product yet
//                $showNurseInfo      = $editedUser->hasRole('care-center');
            }
            $showNurseInfo = false;

            $view->with(compact([
                'usernameField',
                'emailField',
                'firstNameField',
                'lastNameField',
                'practicesField',
                'roleField',
                'internalUserId',
                'showNurseInfo',
                'autoAttachPrograms',
                'nurseInfo',
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