<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\View\Composers;

use App\Jobs\StoreTimeTracking;
use App\Policies\CreateNoteForPatient;
use CircleLinkHealth\CcmBilling\Domain\Patient\PatientServicesForTimeTracker;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummaryView;
use CircleLinkHealth\CcmBilling\Http\Resources\PatientChargeableSummary;
use CircleLinkHealth\CcmBilling\Http\Resources\PatientChargeableSummaryCollection;
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
            $patientId = '';
            $patientProgramId = '';
            $patientFamilyId = null;

            if (isset($patient) && ! empty($patient) && (is_a($patient, User::class) || is_a($patient, Patient::class))) {
                if (is_a($patient, User::class)) {
                    $patientId = $patient->id;
                    $patientProgramId = $patient->program_id;
                    $patientFamilyId = optional($patient->patientInfo)->family_id;
                } elseif (is_a($patient, Patient::class)) {
                    $patientId = $patient->user_id;
                    $patientProgramId = $patient->user->program_id;
                    $patientFamilyId = $patient->family_id;
                }
            }

            $noLiveCountTimeTracking = (isset($noLiveCountTimeTracking) && $noLiveCountTimeTracking);
            if ( ! $noLiveCountTimeTracking) {
                $noLiveCountTimeTracking = ! auth()->user()->isCCMCountable();
            }

            $chargeableServices = $this->getChargeableServices($patientId);

            $view->with(compact([
                'patientId',
                'patientProgramId',
                'enableTimeTracking',
                'urlShort',
                'ipAddr',
                'title',
                'forceSkip',
                'patientFamilyId',
                'noLiveCountTimeTracking',
                'chargeableServices',
                'noBhiSwitch',
            ]));
        });

        View::composer(['partials.providerUItimerComponent'], function ($view) {
            $params = $view->getData();
            if (! isset($params['noLiveCountTimeTracking'])) {
                $noLiveCountTimeTracking = ! auth()->user()->isCCMCountable();
            }
            else {
                $noLiveCountTimeTracking = $params['noLiveCountTimeTracking'];
            }
            
            $view->with(compact([
                'noLiveCountTimeTracking',
            ]));
        });

        View::composer(['partials.userheader', 'wpUsers.patient.careplan.print', 'wpUsers.patient.calls.index'], function ($view) {
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
                $provider = 'N/A';
                $billingDoctor = '';
                $regularDoctor = '';
                $patientIsBhiEligible = false;
                $isAdminOrPatientsAssignedNurse = false;
            }

            $view->with(compact([
                'location',
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

    private function getChargeableServices($patientId)
    {
        if ( ! empty($patientId)) {
            $chargeableServices = (new PatientServicesForTimeTracker((int) $patientId, now()))->get();
        } else {
            $record1                          = new ChargeablePatientMonthlySummaryView();
            $record1->patient_user_id         = $patientId;
            $record1->chargeable_service_id   = -1;
            $record1->chargeable_service_code = 'NONE';
            $record1->chargeable_service_name = 'NONE';
            $record1->total_time              = 0;
            $chargeableServices               = new PatientChargeableSummaryCollection(collect([
                new PatientChargeableSummary($record1),
            ]));
        }

        return $chargeableServices;
    }
}
