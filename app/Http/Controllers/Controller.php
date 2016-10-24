<?php
namespace App\Http\Controllers;

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

        if (\Session::has('inOpenSessionWithPatientId')) {

            $clearPatientSessions = $request->method() == 'GET'
                && str_contains(\URL::previous(), \Session::get('inOpenSessionWithPatientId'))
                && !str_contains($request->getRequestUri(), \Session::get('inOpenSessionWithPatientId'));

            if ($clearPatientSessions) {
                \Session::remove('inOpenSessionWithPatientId');
            }
        }
    }
}