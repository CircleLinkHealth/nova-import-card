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
        $patientId = $request->route('patientId') ?? $request->input('patientId');

        if (!empty($patientId)) {
            if ($request->has('deletePatientSession') && filter_var($request->input('deletePatientSession'),
                    FILTER_VALIDATE_BOOLEAN)
            ) {
                if (auth()->check()) {
                    $user = auth()->user()->ID;
                } else {
                    $user = $request->input('providerId');
                }

                $session = PatientSession::where('user_id', '=', $user)
                    ->where('patient_id', '=', $patientId)
                    ->delete();
            }

            $this->middleware('patient.session');
        }

    }
}