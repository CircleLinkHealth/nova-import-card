<?php
namespace App\Http\Controllers;

use App\Models\PatientSession;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests;

    public function __construct(Request $request)
    {
        $this->middleware('patient.session');

        $patientId = $request->route('patientId') ?? $request->input('patientId');

        $clearPatientSessions = $request->method() == 'GET'
            && str_contains(\URL::previous(), $patientId)
            && !str_contains($request->getRequestUri(), $patientId)
            && !empty($patientId);

        if ($clearPatientSessions) {
            if (auth()->check()) {
                $user = auth()->user()->ID;
            } else {
                $user = $request->input('providerId');
            }

            $session = PatientSession::where('user_id', '=', $user)
                ->where('patient_id', '=', $patientId)
                ->delete();
        }


    }
}