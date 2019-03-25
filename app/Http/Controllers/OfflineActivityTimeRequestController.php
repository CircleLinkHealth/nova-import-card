<?php

namespace App\Http\Controllers;

use CircleLinkHealth\TimeTracking\Entities\Activity;
use App\Http\Requests\CreateOfflineActivityTimeRequest;
use CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;

class OfflineActivityTimeRequestController extends Controller
{
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

        $userTimeZone = $patient->timeZone;

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
                'userTimeZone'            => $userTimeZone,
                'noLiveCountTimeTracking' => true,
            ]
        );
    }

    public function index()
    {
        $requests = OfflineActivityTimeRequest::with('patient')
                                              ->where('requester_id', auth()->id())
                                              ->get();

        return view('care-center.offlineActivityTimeRequest.index')
            ->with('requests', $requests);
    }

    public function adminIndex()
    {
        $requests = OfflineActivityTimeRequest::with('patient')
                                              ->whereNull('is_approved')
                                              ->get();

        return view('care-center.offlineActivityTimeRequest.index')
            ->with('requests', $requests);
    }

    public function adminRespond(Request $request)
    {
        $timeRequest = OfflineActivityTimeRequest::findOrFail($request->input('offline_time_request_id'));

        $isApproved = (boolean)$request->input('approved');

        $timeRequest->is_approved = $isApproved;

        if ($isApproved) {
            $timeRequest->approve();
        } else {
            $timeRequest->reject();
        }

        return redirect()->route('admin.offline-activity-time-requests.index');
    }

    /**
     * @param CreateOfflineActivityTimeRequest $request
     * @param $patientId
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function store(CreateOfflineActivityTimeRequest $request, $patientId)
    {
        $offlineActivityRequest = OfflineActivityTimeRequest::create(
            [
                'type'             => $request->input('type'),
                'comment'          => $request->input('comment'),
                'duration_seconds' => $request->input('duration_minutes') * 60,
                'patient_id'       => $request->input('patient_id'),
                'requester_id'     => auth()->id(),
                'is_behavioral'    => $request->input('is_behavioral'),
                'performed_at'     => \Carbon::parse($request->input('performed_at')),
            ]
        );

        if ($offlineActivityRequest) {
            return redirect()->route('offline-activity-time-requests.index');
        }

        throw new \Exception('Failed saving Offline Activity Time Request', 500);
    }
}
