<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\View\Composers\SAAS\Admin;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ManageInternalUser extends ServiceProvider
{
    /**
     * Register bindings in the container.
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
                $usernameField = $editedUser->username;
                $emailField = $editedUser->email;
                $firstNameField = $editedUser->getFirstName();
                $lastNameField = $editedUser->getLastName();
                $practicesField = $data->get('editedUser')->getPractices();
                $roleField = $data->get('editedUser')->getRole();
                $internalUserId = $editedUser->id;

                //Removing since we are not including this in the SAAS product yet
//                $showNurseInfo      = $editedUser->isCareCoach();
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
     */
    public function register()
    {
    }
}
