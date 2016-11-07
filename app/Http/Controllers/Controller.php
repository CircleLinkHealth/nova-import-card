<?php
namespace App\Http\Controllers;

use App\Models\PatientSession;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct(Request $request)
    {
        $this->middleware('patient.session');

        $patientId = $request->route('patientId') ?? $request->input('patientId');

        if ($request->method() != 'GET') {
            return;
        }

        $clearPatientSessions = preg_match('/(?<![0-9])[0-9]{2,4}(?![0-9])/', $request->getRequestUri()) == 0;

        if (!empty($patientId)) {
            $clearPatientSessions = !str_contains($request->getRequestUri(),
                $patientId)//    && str_contains(\URL::previous(), $patientId)
            ;
        }


        if ($clearPatientSessions) {
            if (auth()->check()) {
                $user = auth()->user()->id;
            } else {
                $user = $request->input('providerId');
            }

            $session = PatientSession::where('user_id', '=', $user)
                ->delete();
        }


    }
}