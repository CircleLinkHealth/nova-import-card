<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\View\Composers;

use CircleLinkHealth\CcmBilling\Domain\Patient\PatientServicesForTimeTracker;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummaryView;
use CircleLinkHealth\CcmBilling\Http\Resources\PatientChargeableSummary;
use CircleLinkHealth\CcmBilling\Http\Resources\PatientChargeableSummaryCollection;
use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Policies\CreateNoteForPatient;
use CircleLinkHealth\TimeTracking\Jobs\StoreTimeTracking;
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
            $params = $view->getData();
            $isForEnrollment = $params['forEnrollment'] ?? false;

            if ( ! isset($activity)) {
                $activity = 'Undefined';
            }

            $route = Route::current();
            $routeName = $route->getName();

            //fall back to uri if route name is null
            $title = empty($routeName) ? $route->uri : $routeName;
            $forceSkip = $isForEnrollment ? false : in_array($title, StoreTimeTracking::UNTRACKED_ROUTES);

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

            $chargeableServices = $this->getChargeableServices($patientId, $isForEnrollment);
            $enrolleeId = $isForEnrollment ? '0' : null;
            $isFromCaPanel = $isForEnrollment;

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
                'enrolleeId',
                'isFromCaPanel',
            ]));
        });

        View::composer(['partials.providerUItimerComponent'], function ($view) {
            $params = $view->getData();
            if ( ! isset($params['noLiveCountTimeTracking'])) {
                $noLiveCountTimeTracking = ! auth()->user()->isCCMCountable();
            } else {
                $noLiveCountTimeTracking = $params['noLiveCountTimeTracking'];
            }

            $disableTimeTracking = isset($params['disableTimeTracking']) && $params['disableTimeTracking'];
            if (app('impersonate')->isImpersonating()) {
                $disableTimeTracking = true;
            }

            $view->with(compact([
                'noLiveCountTimeTracking',
                'disableTimeTracking',
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
    
                $consecutiveUnsuccessfulCallCount = $patient->patientInfo->no_call_attempts_since_last_success;
                $consecutiveUnsuccessfulCallLimit = \CircleLinkHealth\Customer\Repositories\PatientWriteRepository::MARK_UNREACHABLE_AFTER_FAILED_ATTEMPTS;
    
                if($consecutiveUnsuccessfulCallCount < 4) {
                    $consecutiveUnsuccessfulCallColor = '#008000';
                } elseif($consecutiveUnsuccessfulCallCount == 4) {
                    $consecutiveUnsuccessfulCallColor = '#FFA100';
                } elseif($consecutiveUnsuccessfulCallCount == 5) {
                    $consecutiveUnsuccessfulCallColor = '#FF0000';
                }
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
                'consecutiveUnsuccessfulCallCount',
                'consecutiveUnsuccessfulCallLimit',
                'consecutiveUnsuccessfulCallColor',
            ]));
        });
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
    }

    private function getChargeableServices($patientId, bool $isForEnrollment = false)
    {
        if ( ! empty($patientId)) {
            $chargeableServices = (new PatientServicesForTimeTracker((int) $patientId, now()))->get();
        } else {
            $record1                          = new ChargeablePatientMonthlySummaryView();
            $record1->patient_user_id         = $patientId;
            $record1->chargeable_service_id   = -1;
            $record1->chargeable_service_code = 'NONE';
            $record1->chargeable_service_name = 'NONE';

            if ($isForEnrollment) {
                /** @var User $user */
                $user                = auth()->user();
                $record1->total_time = optional(\CircleLinkHealth\SharedModels\Entities\CareAmbassadorLog::createOrGetLogs($user->careAmbassador->id))->total_time_in_system ?? 0;
            } else {
                $record1->total_time = 0;
            }
            $chargeableServices = new PatientChargeableSummaryCollection(collect([
                new PatientChargeableSummary($record1),
            ]));
        }

        return $chargeableServices;
    }
}