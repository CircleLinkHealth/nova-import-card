<?php

namespace App\Http\Controllers;

use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CCMComplexToggleController extends Controller
{

    public function toggle(Request $request,
                           $patientId){

        $patient = User::find($patientId)
                ->patientInfo
                ->patientSummaries
                ->where('month_year', Carbon::now()
                                        ->firstOfMonth()
                                        ->toDateString())->first();

        $input = $request->all();

        if(isset($input['complex'])){
            $patient->is_ccm_complex = 1;
            $patient->save();
        } else {
            $patient->is_ccm_complex = 0;
            $patient->save();
        }

        return redirect()->back();

    }

}
