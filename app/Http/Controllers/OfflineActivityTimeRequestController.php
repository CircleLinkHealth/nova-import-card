<?php

namespace App\Http\Controllers;

use App\Activity;
use App\Http\Requests\CreateOfflineActivityTimeRequest;
use App\OfflineActivityTimeRequest;
use App\User;
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
                'program_id'     => $patient->program_id,
                'patient'        => $patient,
                'patient_name'   => $patient_name,
                'activity_types' => Activity::input_activity_types(),
                'userTimeZone'   => $userTimeZone,
            ]
        );
    }
    
    public function index()
    {
        $requests = OfflineActivityTimeRequest::with('patient')
                                              ->get();
        
        return view('care-center.offlineActivityTimeRequest.index')
            ->with('requests', $requests);
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
                'performed_at'     => $request->input('performed_at'),
            ]
        );
        
        if ($offlineActivityRequest) {
            return redirect()->route('offline-activity-time-requests.index');
        }
        
        throw new \Exception('Failed saving Offline Activity Time Request', 500);
    }
}
