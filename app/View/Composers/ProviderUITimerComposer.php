<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\View\Composers;

use App\Constants;
use App\Jobs\StoreTimeTracking;
use App\Policies\CreateNoteForPatient;
use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
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

            $route = Route::current();
            $routeName = $route->getName();

            //fall back to uri if route name is null
            $title = empty($routeName) ? $route->uri : $routeName;
            $forceSkip = in_array($title, StoreTimeTracking::UNTRACKED_ROUTES);

            $ipAddr = Request::ip();

            $requestUri = Request::getRequestUri();
            $pieces = explode('?', $requestUri);
            $urlShort = $pieces[0];

            $manager = app('impersonate');

            if ($manager->isImpersonating()) {
                $disableTimeTracking = true;
            }

            $enableTimeTracking = ! isset($disableTimeTracking);

            if (false !== strpos($requestUri, 'login')) {
                $enableTimeTracking = false;
            }

            $noBhiSwitch = ! auth()->user()->isCareCoach();

            $patient = $view->patient;

            if (isset($patient) && ! empty($patient) && is_a($patient, User::class)) {
                $patientId = $patient->id;
                $patientProgramId = $patient->program_id;
                $ccm_time = $patient->getCcmTime();
                $bhi_time = $patient->getBhiTime();

                $monthlyTime = $patient->formattedTime($ccm_time);
                $monthlyBhiTime = $patient->formattedTime($bhi_time);

                $noBhiSwitch = $noBhiSwitch || ! $patient->preferredContactLocationHasServices(ChargeableService::BHI);
            } elseif (isset($patient) || ! empty($patient) && is_a($patient, Patient::class)) {
                $patientId = $patient->user_id;
                $patientProgramId = $patient->user->program_id;
                $ccm_time = $patient->user->getCcmTime();
                $bhi_time = $patient->user->getBhiTime();
                $monthlyTime = $patient->user->formattedTime($ccm_time);
                $monthlyBhiTime = $patient->user->formattedTime($bhi_time);

                $noBhiSwitch = $noBhiSwitch || ! $patient->locationHasServices(ChargeableService::BHI);
            } else {
                $monthlyTime = '';
                $monthlyBhiTime = '';
                $patientId = '';
                $patientProgramId = '';
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
                'forceSkip',
            ]));
        });

        View::composer(['partials.userheader', 'wpUsers.patient.careplan.print', 'wpUsers.patient.calls.index'], function ($view) {
            // calculate display, fix bug where gmdate('i:s') doesnt work for > 24hrs

            /**
             * @var User
             */
            $patient = $view->patient;

            if ($patient) {
                $patient->load([
                    'careTeamMembers.user',
                    'patientInfo.location',
                ]);

                $isAdminOrPatientsAssignedNurse = auth()->user()->isAdmin()
                    || auth()->user()->isCareCoach() && app(CreateNoteForPatient::class)->can(auth()->id(), $patient->id);

                $ccmSeconds = $patient->getCcmTime();
                $bhiSeconds = $patient->getBhiTime();
                $monthlyTime = $patient->formattedTime($ccmSeconds);
                $monthlyBhiTime = $patient->formattedTime($bhiSeconds);

                $ccm_above = false;
                if ($ccmSeconds >= Constants::MONTHLY_BILLABLE_TIME_TARGET_IN_SECONDS) {
                    $ccm_above = true;
                }

                $regularDoctor = optional($patient->careTeamMembers->where('type', '=', CarePerson::REGULAR_DOCTOR)->first())->user;
                $billingDoctor = optional($patient->careTeamMembers->where('type', '=', CarePerson::BILLING_PROVIDER)->first())->user;

                $provider = optional($billingDoctor)->getFullName() ?? 'No Provider Selected';

                $preferredLocationName = $patient->getPreferredLocationName();
                $location = empty($preferredLocationName)
                    ? 'Not Set'
                    : $preferredLocationName;

                $patientIsBhiEligible = $patient->isBhi();
            } else {
                $ccm_above = false;
                $location = 'N/A';
                $monthlyTime = sprintf('%02d:%02d:%02d', 0, 0, 0);
                $monthlyBhiTime = sprintf('%02d:%02d:%02d', 0, 0, 0);
                $provider = 'N/A';
                $billingDoctor = '';
                $regularDoctor = '';
                $patientIsBhiEligible = false;
                $isAdminOrPatientsAssignedNurse = false;
            }

            $view->with(compact([
                'ccm_above',
                'location',
                'monthlyTime',
                'monthlyBhiTime',
                'provider',
                'regularDoctor',
                'billingDoctor',
                'patientIsBhiEligible',
                'isAdminOrPatientsAssignedNurse',
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
