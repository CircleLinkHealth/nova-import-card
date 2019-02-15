<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\View\Composers;

use App\Patient;
use App\User;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ProviderUITimerComposer extends ServiceProvider
{
    /**
     * Register bindings in the container.
     */
    public function boot()
    {
        View::composer(['partials.providerUItimer'], function ($view) {
            $ccm_time = 0;
            $bhi_time = 0;

            if ( ! isset($activity)) {
                $activity = 'Undefined';
            }

            $title = Route::currentRouteName();

            $ipAddr = Request::ip();

            $requestUri = Request::getRequestUri();
            $pieces = explode('?', $requestUri);
            $urlShort = $pieces[0];

            $manager = app('impersonate');

            if ($manager->isImpersonating()) {
                $disableTimeTracking = true;
            }

            $enableTimeTracking = ! isset($disableTimeTracking);

            // disable if login
            if (false !== strpos($requestUri, 'login')) {
                $enableTimeTracking = false;
            }

            // do NOT show BHI switch if user does not have care-center role
            $noBhiSwitch = ! auth()->user()->hasRole('care-center');

            $patient = $view->patient;
            $patientId = '';
            $patientProgramId = '';
            if (isset($patient) && ! empty($patient) && is_a($patient, User::class)) {
                $patientId = $patient->id;
                $patientProgramId = $patient->program_id;
                $ccm_time = $patient->getCcmTime();
                $bhi_time = $patient->getBhiTime();

                $monthlyTime = $patient->formattedTime($ccm_time);
                $monthlyBhiTime = $patient->formattedTime($bhi_time);

                //also, do NOT show BHI switch if user's primary practice is not being charged for CPT 99484
                $noBhiSwitch = $noBhiSwitch || ! optional($patient->primaryPractice()->first())->hasServiceCode('CPT 99484');
            } elseif (isset($patient) || ! empty($patient) && is_a($patient, Patient::class)) {
                $patientId = $patient->user_id;
                $patientProgramId = $patient->user->program_id;
                $ccm_time = $patient->user->getCcmTime();
                $bhi_time = $patient->user->getBhiTime();
                $monthlyTime = $patient->user->formattedTime($ccm_time);
                $monthlyBhiTime = $patient->user->formattedTime($bhi_time);
                //also, do NOT show BHI switch if user's primary practice is not being charged for CPT 99484
                $noBhiSwitch = $noBhiSwitch || ! optional($patient->user->primaryPractice()->first())->hasServiceCode('CPT 99484');
            }
            else {
                $monthlyTime = '';
                $monthlyBhiTime = '';
            }

            $view->with(compact([
                'patientId',
                'patientProgramId',
                'enableTimeTracking',
                'urlShort',
                'ipAddr',
                'title',
                'ccm_time',
                'bhi_time',
                'noBhiSwitch',
                'monthlyTime',
                'monthlyBhiTime',
            ]));
        });

        View::composer(['partials.userheader', 'wpUsers.patient.careplan.print', 'wpUsers.patient.calls.index'], function ($view) {
            // calculate display, fix bug where gmdate('i:s') doesnt work for > 24hrs
            $patient = $view->patient;

            if ($patient) {
                $ccmSeconds = $patient->getCcmTime();
                $monthlyTime = $patient->formattedTime($ccmSeconds);
                $monthlyBhiTime = $patient->formattedBhiTime();

                $ccm_complex = $patient->isCCMComplex() ?? false;

                $ccm_above = false;
                if ($ccmSeconds > 1199 && ! $ccm_complex) {
                    $ccm_above = true;
                } elseif ($ccmSeconds > 3599 && $ccm_complex) {
                    $ccm_above = true;
                }

                $regularDoctor = $patient->regularDoctorUser();
                $billingDoctor = $patient->billingProviderUser();

                $provider = optional($billingDoctor)->getFullName() ?? 'No Provider Selected';

                $location = empty($patient->getPreferredLocationName())
                    ? 'Not Set'
                    : $patient->getPreferredLocationName();
            } else {
                $ccm_above = false;
                $ccm_complex = false;
                $location = 'N/A';
                $monthlyTime = sprintf('%02d:%02d:%02d', 0, 0, 0);
                $monthlyBhiTime = sprintf('%02d:%02d:%02d', 0, 0, 0);
                $provider = 'N/A';
                $billingDoctor = '';
                $regularDoctor = '';
            }

            $view->with(compact([
                'ccm_above',
                'ccm_complex',
                'location',
                'monthlyTime',
                'monthlyBhiTime',
                'provider',
                'regularDoctor',
                'billingDoctor',
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
