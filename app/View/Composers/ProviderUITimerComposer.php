<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\View\Composers;

use App\Constants;
use CircleLinkHealth\TimeTracking\Jobs\StoreTimeTracking;
use App\Policies\CreateNoteForPatient;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\CarePerson;
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

            // disable if login
            if (false !== strpos($requestUri, 'login')) {
                $enableTimeTracking = false;
            }

            // do NOT show BHI switch if user does not have care-center role
            $noBhiSwitch = ! auth()->user()->isCareCoach();

            $patient = $view->patient;

            if (isset($patient) && ! empty($patient) && is_a($patient, User::class)) {
                $patientId = $patient->id;
                $patientProgramId = $patient->program_id;
                $ccm_time = $patient->getCcmTime();
                $bhi_time = $patient->getBhiTime();

                $monthlyTime = $patient->formattedTime($ccm_time);
                $monthlyBhiTime = $patient->formattedTime($bhi_time);

                //also, do NOT show BHI switch if user's primary practice is not being charged for CPT 99484
                $noBhiSwitch = $noBhiSwitch || ! optional($patient->primaryPractice)->hasServiceCode('CPT 99484');
            } elseif (isset($patient) || ! empty($patient) && is_a($patient, Patient::class)) {
                $patientId = $patient->user_id;
                $patientProgramId = $patient->user->program_id;
                $ccm_time = $patient->user->getCcmTime();
                $bhi_time = $patient->user->getBhiTime();
                $monthlyTime = $patient->user->formattedTime($ccm_time);
                $monthlyBhiTime = $patient->user->formattedTime($bhi_time);
                //also, do NOT show BHI switch if user's primary practice is not being charged for CPT 99484
                $noBhiSwitch = $noBhiSwitch || ! optional($patient->user->primaryPractice)->hasServiceCode('CPT 99484');
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
            $patient = $view->patient;

            if ($patient) {
                $patient->load([
                    'patientSummaries' => function ($pms) {
                        $pms->select(['bhi_time', 'ccm_time', 'id'])
                            ->orderBy('id', 'desc')
                            ->whereMonthYear(Carbon::now()->startOfMonth());
                    },
                    'careTeamMembers.user',
                    'patientInfo.location',
                ]);

                $isAdminOrPatientsAssignedNurse = auth()->user()->isAdmin()
                    || auth()->user()->isCareCoach() && app(CreateNoteForPatient::class)->can(auth()->id(), $patient->id);

                $currentPms = $patient->patientSummaries->first();

                $ccmSeconds = $currentPms->ccm_time ?? 0;
                $bhiSeconds = $currentPms->bhi_time ?? 0;
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

                $patientIsBhiEligible = optional($patient->primaryPractice)->hasServiceCode('CPT 99484') && ($bhiSeconds || $patient->isBhi());
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
