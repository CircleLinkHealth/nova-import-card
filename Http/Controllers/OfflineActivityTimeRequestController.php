<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Http\Controllers;

use CircleLinkHealth\CcmBilling\Domain\Patient\PatientServicesForTimeTracker;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Http\Requests\CreateOfflineActivityTimeRequest;
use CircleLinkHealth\SharedModels\Entities\Activity;
use CircleLinkHealth\SharedModels\Entities\OfflineActivityTimeRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class OfflineActivityTimeRequestController extends Controller
{
    public function adminIndex()
    {
        $requests = OfflineActivityTimeRequest::with([
            'patient',
            'chargeableService',
        ])
            ->whereNull('is_approved')
            ->get();

        return view('customer::admin.offlineActivityTimeRequest.index')
            ->with('requests', $requests);
    }

    public function adminRespond(Request $request)
    {
        $timeRequest = OfflineActivityTimeRequest::findOrFail($request->input('offline_time_request_id'));

        $isApproved = (bool) $request->input('approved');

        $timeRequest->is_approved = $isApproved;

        if ($isApproved) {
            $timeRequest->approve();
        } else {
            $timeRequest->reject();
        }

        return redirect()->route('admin.offline-activity-time-requests.index');
    }

    public function create($patientId)
    {
        if ( ! $patientId) {
            return abort(404);
        }

        $patient = User::find($patientId);

        if ( ! $patient) {
            return response('User not found', 401);
        }

        $patient_name = $patient->getFullName();

        $userTimeZone = $patient->timezone;

        if (empty($userTimeZone)) {
            $userTimeZone = 'America/New_York';
        }

        return view(
            'care-center.offlineActivityTimeRequest.create',
            [
                'program_id'              => $patient->program_id,
                'patient'                 => $patient,
                'patient_name'            => $patient_name,
                'activity_types'          => Activity::input_activity_types(),
                'chargeableServices'      => $this->getChargeableServices($patient->id),
                'userTimeZone'            => $userTimeZone,
                'noLiveCountTimeTracking' => true,
            ]
        );
    }

    public function index()
    {
        $requests = OfflineActivityTimeRequest::with([
            'patient',
            'chargeableService',
        ])
            ->where('requester_id', auth()->id())
            ->get();

        return view('customer::admin.offlineActivityTimeRequest.index')
            ->with('requests', $requests);
    }

    /**
     * @param $patientId
     *
     * @throws \Exception
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(CreateOfflineActivityTimeRequest $request, $patientId)
    {
        $offlineActivityRequest = OfflineActivityTimeRequest::create(
            [
                'type'                  => $request->input('type'),
                'comment'               => $request->input('comment'),
                'duration_seconds'      => $request->input('duration_minutes') * 60,
                'patient_id'            => $request->input('patient_id'),
                'requester_id'          => auth()->id(),
                'is_behavioral'         => $request->input('is_behavioral'),
                'chargeable_service_id' => $request->input('chargeable_service_id'),
                'performed_at'          => \Carbon::parse($request->input('performed_at')),
            ]
        );

        if ($offlineActivityRequest) {
            return redirect()->route('offline-activity-time-requests.index');
        }

        throw new \Exception('Failed saving Offline Activity Time Request', 500);
    }

    private function getChargeableServices($patientId)
    {
        return (new PatientServicesForTimeTracker((int) $patientId, now()))->get();
    }
}
