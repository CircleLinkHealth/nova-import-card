<?php

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
     *
     * @return void
     */
    public function boot()
    {
        View::composer(['partials.providerUItimer'], function ($view) {
            if ( ! isset($activity)) {
                $activity = 'Undefined';
            }

            $title = Route::currentRouteName();

            $ipAddr = Request::ip();

            $requestUri = Request::getRequestUri();
            $pieces     = explode("?", $requestUri);
            $urlShort   = $pieces[0];

            $enableTimeTracking = ! isset($disableTimeTracking);

            // disable if login
            if (strpos($requestUri, 'login') !== false) {
                $enableTimeTracking = false;
            }

            $patientId        = '';
            $patientProgramId = '';
            if (isset($patient) && ! empty($patient) && is_a($patient, User::class)) {
                $patientId        = $patient->id;
                $patientProgramId = $patient->program_id;
            } elseif (isset($patient) && ! empty($patient) && is_a($patient, Patient::class)) {
                $patientId        = $patient->user_id;
                $patientProgramId = $patient->user->program_id;
            }

            $view->with(compact([
                'patientId',
                'patientProgramId',
                'enableTimeTracking',
                'urlShort',
                'ipAddr',
                'title',
            ]));
        });


        View::composer(['partials.userheader', 'wpUsers.patient.careplan.print'], function ($view) {
            // calculate display, fix bug where gmdate('i:s') doesnt work for > 24hrs
            $patient = $view->patient;

            $seconds = optional($patient->patientInfo)->cur_month_activity_time ?? 0;

            $H           = floor($seconds / 3600);
            $i           = ($seconds / 60) % 60;
            $s           = $seconds % 60;
            $monthlyTime = sprintf("%02d:%02d:%02d", $H, $i, $s);
            $ccm_above   = false;

            $ccm_complex = false;
            if ($patient->patientInfo) {
                $ccm_complex = $patient->patientInfo->isCCMComplex() ?? false;
            }

            if ($seconds > 1199 && ! $ccm_complex) {
                $ccm_above = true;
            } elseif ($seconds > 3599 && $ccm_complex) {
                $ccm_above = true;
            }

            $provider = optional($patient->billingProviderUser())->fullName ?? 'No Provider Selected';

            $location = empty($patient->getPreferredLocationName())
                ? 'Not Set'
                : $patient->getPreferredLocationName();

            $view->with(compact([
                'ccm_above',
                'ccm_complex',
                'location',
                'monthlyTime',
                'provider',
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
